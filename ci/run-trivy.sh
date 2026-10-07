#!/bin/sh
# Analyse de vulnérabilités / secrets / mauvaises configurations avec Trivy.
#   sh ci/run-trivy.sh fs            -> le dépôt (code, Dockerfile, Jenkinsfile) AVANT le build
#   sh ci/run-trivy.sh image <ref>   -> l'image construite
# Sévérités bloquantes : HIGH et CRITICAL (corrections disponibles uniquement pour l'image).
set -eu
MODE="${1:?fs|image}"
TRIVY="aquasec/trivy:0.58.1"
CACHE="-v trivy-cache:/root/.cache/"
case "$MODE" in
  fs)
    NAME="ttt-trivy-$(printf '%s' "${BUILD_TAG:-local}" | tr -c 'a-zA-Z0-9' '-' | cut -c1-40)"
    docker rm -f "$NAME" >/dev/null 2>&1 || true
    # shellcheck disable=SC2086
    docker create --name "$NAME" $CACHE --entrypoint trivy "$TRIVY" \
      fs --scanners vuln,secret,misconfig --severity HIGH,CRITICAL --exit-code 1 --skip-dirs /scan/.git /scan >/dev/null
    PATHS=""; for p in src Dockerfile Dockerfile.ci apache.conf docker docker-compose.yml Jenkinsfile ci tests monitoring router; do [ -e "$p" ] && PATHS="$PATHS $p"; done
    # shellcheck disable=SC2086
    tar -c $PATHS | docker cp - "$NAME":/scan
    set +e; docker start -a "$NAME"; RC=$?; set -e
    docker rm -f "$NAME" >/dev/null 2>&1 || true
    exit $RC ;;
  image)
    REF="${2:?référence d'image}"
    # shellcheck disable=SC2086
    docker run --rm $CACHE -v /var/run/docker.sock:/var/run/docker.sock "$TRIVY" \
      image --severity HIGH,CRITICAL --ignore-unfixed --exit-code 1 "$REF" ;;
  *) echo "Mode inconnu : $MODE" >&2; exit 2 ;;
esac
