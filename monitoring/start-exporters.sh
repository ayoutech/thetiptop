#!/bin/sh
# Démarre les deux exportateurs de métriques sur le réseau Docker des outils (furious-network) :
#   - node-exporter : CPU, RAM, disque, réseau de la VM
#   - cAdvisor      : CPU, RAM, réseau de chaque conteneur
# À lancer UNE fois sur la VM ; --restart unless-stopped les relance après un redémarrage.
set -eu
NET=furious-network

docker rm -f node-exporter cadvisor >/dev/null 2>&1 || true

docker run -d --name node-exporter --restart unless-stopped --network "$NET" \
  --pid host -v /:/host:ro,rslave \
  quay.io/prometheus/node-exporter:v1.8.2 --path.rootfs=/host

docker run -d --name cadvisor --restart unless-stopped --network "$NET" \
  --volume /:/rootfs:ro --volume /var/run:/var/run:ro --volume /sys:/sys:ro \
  --volume /var/lib/docker/:/var/lib/docker:ro --volume /dev/disk/:/dev/disk:ro \
  --privileged --device /dev/kmsg \
  gcr.io/cadvisor/cadvisor:v0.49.1

# Prometheus doit être sur le même réseau pour résoudre "node-exporter" et "cadvisor"
docker network connect "$NET" prometheus 2>/dev/null || true
docker ps --filter name=node-exporter --filter name=cadvisor --format 'table {{.Names}}\t{{.Status}}'
