#!/bin/sh
# Exécute UNE suite de tests dans des conteneurs jetables.
#   Usage : sh ci/run-suite.sh <lint|unit|integration|api|e2e|security>
# - L'agent Jenkins n'a pas PHP : tout tourne dans l'image d'outillage (Dockerfile.ci).
# - Les suites integration/api/e2e démarrent une base MariaDB 10.11 dédiée (réseau Docker privé).
# - Le code est copié dans le conteneur (docker cp) plutôt que monté : fonctionne même si Jenkins est lui-même un conteneur.
# - Les rapports JUnit sont rapatriés dans ./reports/ ; tout est nettoyé à la fin, même en cas d'échec.
set -eu

SUITE="${1:?usage: run-suite.sh lint|unit|integration|api|e2e|security}"
TAG=$(printf '%s' "${BUILD_TAG:-local}" | tr -c 'a-zA-Z0-9' '-' | cut -c1-40)
NET="ttt-ci-$SUITE-$TAG"; DB="ttt-db-$SUITE-$TAG"; RUN="ttt-run-$SUITE-$TAG"
CI_IMAGE="thetiptop-ci:php82-phpunit105"
mkdir -p reports

cleanup() {
  docker rm -f "$RUN" "$DB" >/dev/null 2>&1 || true
  docker network rm "$NET" >/dev/null 2>&1 || true
}
trap cleanup EXIT
cleanup

echo "==> Image d'outillage $CI_IMAGE (cache Docker)"
docker build -q -t "$CI_IMAGE" - < Dockerfile.ci >/dev/null

case "$SUITE" in
  lint)     CMD='fail=0; for f in $(find src tests -name "*.php"); do php -l "$f" >/dev/null || { echo "Erreur de syntaxe : $f"; fail=1; }; done; [ $fail -eq 0 ] && echo "Syntaxe PHP valide ($(find src tests -name "*.php" | wc -l) fichiers)"; exit $fail' ;;
  unit|security) CMD="phpunit --testsuite $SUITE --testdox --log-junit reports/$SUITE.xml" ;;
  integration|api|e2e) CMD="phpunit --testsuite $SUITE --testdox --log-junit reports/$SUITE.xml" ;;
  *) echo "Suite inconnue : $SUITE" >&2; exit 2 ;;
esac

docker network create "$NET" >/dev/null

DB_ENV=""
case "$SUITE" in integration|api|e2e)
  echo "==> Base MariaDB 10.11 jetable"
  docker run -d --name "$DB" --network "$NET" --network-alias db \
    -e MARIADB_ROOT_PASSWORD=ci_root -e MARIADB_DATABASE=thetiptop_ci \
    mariadb:10.11 --character-set-server=utf8mb4 >/dev/null
  i=0
  until docker exec "$DB" mariadb-admin ping -h127.0.0.1 -uroot -pci_root --silent >/dev/null 2>&1; do
    i=$((i+1))
    if [ "$i" -gt 60 ]; then echo "MariaDB indisponible après 2 min"; docker logs "$DB" | tail -20; exit 1; fi
    sleep 2
  done
  DB_ENV="-e DB_HOST=db -e DB_PORT=3306 -e DB_NAME=thetiptop_ci -e DB_USER=root -e DB_PASS=ci_root"
  ;;
esac

echo "==> Suite « $SUITE »"
# shellcheck disable=SC2086
docker create --name "$RUN" --network "$NET" $DB_ENV "$CI_IMAGE" sh -c "$CMD" >/dev/null

PATHS=""
for p in src tests phpunit.xml docker apache.conf Dockerfile Jenkinsfile docker-compose.yml ci monitoring; do
  [ -e "$p" ] && PATHS="$PATHS $p"
done
# shellcheck disable=SC2086
tar -c $PATHS | docker cp - "$RUN":/app

set +e
docker start -a "$RUN"
RC=$?
set -e
docker cp "$RUN":/app/reports/. reports/ >/dev/null 2>&1 || true
exit $RC
