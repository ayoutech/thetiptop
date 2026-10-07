#!/bin/sh
D=/var/lib/node-textfile
mkdir -p "$D"
docker stats --no-stream --format '{{.Name}} {{.CPUPerc}} {{.MemUsage}}' | awk '
function tobytes(v,   n, u) {
  n = v + 0; u = v; gsub(/[0-9.]/, "", u)
  if (u == "KiB") return n * 1024
  if (u == "MiB") return n * 1048576
  if (u == "GiB") return n * 1073741824
  if (u == "kB")  return n * 1000
  if (u == "MB")  return n * 1000000
  if (u == "GB")  return n * 1000000000
  return n
}
BEGIN {
  print "# TYPE docker_container_cpu_percent gauge"
  print "# TYPE docker_container_memory_bytes gauge"
}
{ gsub(/%/, "", $2)
  printf "docker_container_cpu_percent{name=\"%s\"} %s\n", $1, $2
  printf "docker_container_memory_bytes{name=\"%s\"} %d\n", $1, tobytes($3) }
' > "$D/docker.prom.tmp" && mv "$D/docker.prom.tmp" "$D/docker.prom"
rm -f "$D/docker_names.prom"
