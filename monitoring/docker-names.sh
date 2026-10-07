#!/bin/sh
# Écrit la correspondance identifiant -> nom des conteneurs pour node_exporter (collecteur textfile).
# Nécessaire car cAdvisor ne sait pas lire les noms avec Docker 29. Lancé chaque minute par cron.
D=/var/lib/node-textfile
mkdir -p "$D"
{
  echo '# HELP docker_container_name_info Nom des conteneurs Docker'
  echo '# TYPE docker_container_name_info gauge'
  docker ps --no-trunc --format '{{.ID}} {{.Names}}' | while read -r id name; do
    echo "docker_container_name_info{cid=\"$id\",name=\"$name\"} 1"
  done
} > "$D/docker_names.prom.tmp" && mv "$D/docker_names.prom.tmp" "$D/docker_names.prom"
