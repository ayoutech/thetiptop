#!/bin/sh
# Tests de fumée POST-DÉPLOIEMENT sur un environnement réel (DEV, PREPROD, couleur inactive de PROD).
#   Usage : sh ci/smoke-test.sh <url> [sha]
# Si <sha> est fourni, attend d'abord que l'environnement serve CETTE version (en-tête X-Release) :
# c'est ce qui évite de tester l'ancienne instance pendant que Render déploie la nouvelle.
set -u
URL="${1:?url}"; URL="${URL%/}"; SHA="${2:-}"
FAIL=0

if [ -n "$SHA" ]; then
  echo "==> Attente de la version $SHA sur $URL (6 min max, démarrage à froid Render inclus)"
  i=0
  while :; do
    REL=$(curl -s -m 30 -o /dev/null -D - "$URL/" | tr -d '\r' | awk -F': ' 'tolower($1)=="x-release"{print $2}')
    case "$REL" in "$SHA"*) [ -n "$REL" ] && { echo "OK version servie : $REL"; break; } ;; esac
    i=$((i+1))
    if [ "$i" -ge 36 ]; then echo "ECHEC : la version $SHA n'est pas servie après 6 min (vue : '${REL:-aucune}')"; exit 1; fi
    sleep 10
  done
fi

ok()   { echo "  OK   $1"; }
ko()   { echo "  ECHEC $1"; FAIL=1; }
status() { curl -s -m 30 -o /dev/null -w '%{http_code}' "$@"; }

echo "==> Tests de fumée sur $URL"
[ "$(status "$URL/")" = "200" ]                          && ok "accueil 200"                        || ko "accueil != 200"
for p in /pages/connexion.php /pages/inscription.php /pages/reglement.php /pages/mentions-legales.php; do
  [ "$(status "$URL$p")" = "200" ]                       && ok "$p 200"                             || ko "$p != 200"
done
curl -s -m 30 "$URL/" | grep -q "Thé Tip Top"           && ok "contenu de l'accueil"               || ko "contenu de l'accueil"
[ "$(status "$URL/pages/admin.php")" = "302" ]           && ok "espace admin protégé (302)"         || ko "espace admin non protégé"
API=$(status "$URL/api/verify-code.php?code=ABCDE12345")   # 401 (non signé) ou 400 (HTTP simple) : jamais 200
case "$API" in 400|401) ok "API sans signature refusée ($API)" ;; *) ko "API accessible sans signature (HTTP $API)" ;; esac
[ "$(status "$URL/config/database.php")" = "403" ]       && ok "dossier config interdit (403)"      || ko "dossier config accessible"

HDRS=$(curl -s -m 30 -o /dev/null -D - "$URL/" | tr -d '\r')
echo "$HDRS" | grep -qi '^x-content-type-options: nosniff'  && ok "en-tête X-Content-Type-Options"  || ko "X-Content-Type-Options absent"
echo "$HDRS" | grep -qi '^x-frame-options:'                  && ok "en-tête X-Frame-Options"         || ko "X-Frame-Options absent"
echo "$HDRS" | grep -qi '^server: Apache/[0-9]'              && ko "version d'Apache exposée"        || ok "version d'Apache masquée"
echo "$HDRS" | grep -qi '^x-powered-by:'                     && ko "X-Powered-By exposé"             || ok "X-Powered-By masqué"

T=$(curl -s -m 30 -o /dev/null -w '%{time_total}' "$URL/")
awk "BEGIN{exit !($T < 3)}" && ok "temps de réponse accueil ${T}s (< 3 s)" || ko "accueil lent : ${T}s"

if [ "$FAIL" -eq 0 ]; then echo "==> Tests de fumée : SUCCÈS"; else echo "==> Tests de fumée : ÉCHEC"; fi
exit $FAIL
