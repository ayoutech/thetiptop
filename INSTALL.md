# Installation (à copier à la racine du dépôt, branche develop)

1. Copier tout le contenu de ce zip dans le dépôt (écrase Dockerfile, Jenkinsfile, apache.conf, src/…).
2. Vérifier que `API_HMAC_SECRET` est défini sur Render pour dev, preprod, blue et green (sinon l'API répond 500 par sécurité).
3. Commit (sans attribution IA) puis push sur develop.
4. Sur la VM : `bash monitoring/start-exporters.sh` (node_exporter + cAdvisor sur furious-network).
5. Ajouter `monitoring/prometheus-scrape-additions.yml` à /tmp/prometheus.yml (`sudo tee -a`), `promtool check config`, `docker restart prometheus`.
6. Grafana : Import > grafana-thetiptop-infra.json > choisir la datasource Prometheus.
7. Premier build Jenkins : plus long (construction de l'image CI, image Playwright ~1,5 Go).
Étapes : lint, unitaires, intégration, API, sécurité(+Trivy), E2E HTTP, E2E Chromium, build image, Trivy image, DEV, k6, PREPROD, approbation, PROD.
