#!/bin/sh
# Tests end-to-end dans un VRAI navigateur (Chromium piloté par Playwright).
#   Usage : sh ci/run-browser-tests.sh
# Architecture : [MariaDB] <- [app : php -S sur le code du dépôt] <- [Playwright + Chromium]
# Tout est jetable et relié par un réseau Docker privé ; rapport JUnit dans reports/e2e-browser.xml.
set -eu

TAG=$(printf '%s' "${BUILD_TAG:-local}" | tr -c 'a-zA-Z0-9' '-' | cut -c1-40)
NET="ttt-ci-browser-$TAG"; DB="ttt-db-browser-$TAG"; APP="ttt-app-browser-$TAG"; PW="ttt-pw-browser-$TAG"
CI_IMAGE="thetiptop-ci:php82-phpunit105"
PW_VERSION="1.56.1"
PW_IMAGE="mcr.microsoft.com/playwright:v${PW_VERSION}-noble"
mkdir -p reports

cleanup() { docker rm -f "$PW" "$APP" "$DB" >/dev/null 2>&1 || true; docker network rm "$NET" >/dev/null 2>&1 || true; }
trap cleanup EXIT
cleanup

docker build -q -t "$CI_IMAGE" - < Dockerfile.ci >/dev/null
docker network create "$NET" >/dev/null

echo "==> MariaDB"
docker run -d --name "$DB" --network "$NET" --network-alias db \
  -e MARIADB_ROOT_PASSWORD=ci_root -e MARIADB_DATABASE=thetiptop_ci mariadb:10.11 --character-set-server=utf8mb4 >/dev/null
i=0
until docker exec "$DB" mariadb-admin ping -h127.0.0.1 -uroot -pci_root --silent >/dev/null 2>&1; do
  i=$((i+1)); [ "$i" -gt 60 ] && { echo "MariaDB indisponible"; exit 1; }; sleep 2
done

echo "==> Application (code du dépôt, serveur PHP)"
docker create --name "$APP" --network "$NET" --network-alias app \
  -e DB_HOST=db -e DB_PORT=3306 -e DB_NAME=thetiptop_ci -e DB_USER=root -e DB_PASS=ci_root \
  "$CI_IMAGE" sh -c 'php tests/browser/seed.php && ARGS=$(sed -n "s/^\([a-z_.]*\) *= *\(.*\)\$/-d \1=\2/p" docker/security.ini) && exec php $ARGS -S 0.0.0.0:8080 -t src' >/dev/null
# docker/security.ini = configuration PHP de production ; le serveur PHP en ligne de commande exige des options -d
# (il ignore output_buffering dans un fichier .ini), construites ici à partir de ce même fichier
tar -c src tests docker phpunit.xml | docker cp - "$APP":/app
docker start "$APP" >/dev/null
i=0
until docker exec "$APP" php -r 'exit(@file_get_contents("http://127.0.0.1:8080/") === false ? 1 : 0);' >/dev/null 2>&1; do
  i=$((i+1)); [ "$i" -gt 30 ] && { echo "Application indisponible"; docker logs "$APP" | tail -20; exit 1; }; sleep 2
done

echo "==> Navigateur (Playwright $PW_VERSION)"
docker create --name "$PW" --network "$NET" --ipc=host -w /w \
  -e BASE_URL=http://app:8080 -e REPORT_DIR=/w/reports \
  "$PW_IMAGE" sh -c "npm init -y >/dev/null 2>&1 && npm install --silent playwright@${PW_VERSION} && node tests/browser/e2e-browser.js" >/dev/null
tar -c tests/browser | docker cp - "$PW":/w
set +e
docker start -a "$PW"
RC=$?
set -e
docker cp "$PW":/w/reports/. reports/ >/dev/null 2>&1 || true
exit $RC
