#!/bin/sh
# Test de performance k6 (script transmis par stdin : aucun volume à monter).
#   Usage : sh ci/run-perf.sh <url>
set -eu
URL="${1:?url}"
docker run --rm -i -e TARGET="${URL%/}" grafana/k6:0.54.0 run - < tests/perf/smoke.js
