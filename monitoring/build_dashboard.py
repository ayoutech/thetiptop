#!/usr/bin/env python3
"""Génère monitoring/grafana-thetiptop-infra.json (dashboard Grafana importable)."""
import json

DS = {"type": "prometheus", "uid": "${DS_PROMETHEUS}"}
_id = [0]

def nid():
    _id[0] += 1
    return _id[0]

def target(expr, legend="", ref="A"):
    return {"datasource": DS, "expr": expr, "legendFormat": legend, "refId": ref, "editorMode": "code", "range": True}

def stat(title, expr, unit, x, y, w=4, h=4, steps=None, minv=None, maxv=None, desc=""):
    steps = steps or [{"color": "green", "value": None}]
    d = {"id": nid(), "type": "stat", "title": title, "description": desc, "datasource": DS,
         "gridPos": {"x": x, "y": y, "w": w, "h": h},
         "targets": [target(expr)],
         "options": {"reduceOptions": {"calcs": ["lastNotNull"], "fields": "", "values": False},
                     "colorMode": "background", "graphMode": "area", "textMode": "auto", "orientation": "auto"},
         "fieldConfig": {"defaults": {"unit": unit, "thresholds": {"mode": "absolute", "steps": steps}}, "overrides": []}}
    if minv is not None: d["fieldConfig"]["defaults"]["min"] = minv
    if maxv is not None: d["fieldConfig"]["defaults"]["max"] = maxv
    return d

def ts(title, targets, unit, x, y, w=12, h=8, stack=False, desc="", minv=None, maxv=None):
    d = {"id": nid(), "type": "timeseries", "title": title, "description": desc, "datasource": DS,
         "gridPos": {"x": x, "y": y, "w": w, "h": h},
         "targets": [target(e, l, chr(65 + i)) for i, (e, l) in enumerate(targets)],
         "options": {"legend": {"displayMode": "table", "placement": "bottom", "calcs": ["mean", "max", "lastNotNull"]},
                     "tooltip": {"mode": "multi", "sort": "desc"}},
         "fieldConfig": {"defaults": {"unit": unit, "custom": {"lineWidth": 1, "fillOpacity": 25 if stack else 10,
                         "showPoints": "never", "stacking": {"mode": "normal" if stack else "none", "group": "A"}}},
                         "overrides": []}}
    if minv is not None: d["fieldConfig"]["defaults"]["min"] = minv
    if maxv is not None: d["fieldConfig"]["defaults"]["max"] = maxv
    return d

def row(title, y):
    return {"id": nid(), "type": "row", "title": title, "collapsed": False, "gridPos": {"x": 0, "y": y, "w": 24, "h": 1}, "panels": []}

pct_steps = [{"color": "green", "value": None}, {"color": "orange", "value": 70}, {"color": "red", "value": 90}]
ROOT_FS = 'mountpoint="/",fstype!~"tmpfs|overlay|squashfs"'
NET_DEV = 'device!~"lo|veth.*|br-.*|docker.*|cni.*|flannel.*"'

panels = []
panels.append(row("Serveur (VM Oracle Cloud) — node_exporter", 0))
panels += [
    stat("CPU utilisé", '100 - (avg(rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100)', "percent", 0, 1, steps=pct_steps, minv=0, maxv=100,
         desc="100 % − part de temps CPU au repos, moyenne de tous les cœurs."),
    stat("RAM utilisée", '(1 - node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes) * 100', "percent", 4, 1, steps=pct_steps, minv=0, maxv=100),
    stat("Disque / utilisé", f'100 - (node_filesystem_avail_bytes{{{ROOT_FS}}} / node_filesystem_size_bytes{{{ROOT_FS}}} * 100)', "percent", 8, 1, steps=pct_steps, minv=0, maxv=100),
    stat("Charge (load 1 min)", 'node_load1', "short", 12, 1, steps=[{"color": "green", "value": None}, {"color": "orange", "value": 2}, {"color": "red", "value": 4}]),
    stat("Disponibilité du serveur", 'time() - node_boot_time_seconds', "s", 16, 1),
    stat("Conteneurs en marche", 'count(container_last_seen{name!=""})', "short", 20, 1),
]
panels += [
    ts("CPU par mode", [('sum by (mode) (rate(node_cpu_seconds_total{mode!="idle"}[5m])) / scalar(count(count by (cpu) (node_cpu_seconds_total))) * 100', "{{mode}}")],
       "percent", 0, 5, stack=True, minv=0, maxv=100, desc="user = applications, system = noyau, iowait = attente disque, steal = hyperviseur."),
    ts("Mémoire (RAM)", [('node_memory_MemTotal_bytes', "Totale"),
                         ('node_memory_MemTotal_bytes - node_memory_MemAvailable_bytes', "Utilisée"),
                         ('node_memory_Cached_bytes + node_memory_Buffers_bytes', "Cache + tampons"),
                         ('node_memory_MemAvailable_bytes', "Disponible")], "bytes", 12, 5, minv=0),
    ts("Trafic réseau (interfaces physiques)", [(f'sum(rate(node_network_receive_bytes_total{{{NET_DEV}}}[5m]))', "Reçu"),
                                               (f'sum(rate(node_network_transmit_bytes_total{{{NET_DEV}}}[5m]))', "Émis")], "Bps", 0, 13),
    ts("Entrées / sorties disque", [('sum(rate(node_disk_read_bytes_total[5m]))', "Lecture"),
                                    ('sum(rate(node_disk_written_bytes_total[5m]))', "Écriture")], "Bps", 12, 13),
]
panels.append(row("Conteneurs Docker — cAdvisor", 21))
panels += [
    ts("CPU par conteneur", [('sum by (name) (rate(container_cpu_usage_seconds_total{name!=""}[5m])) * 100', "{{name}}")], "percent", 0, 22, stack=True,
       desc="100 % = un cœur complet."),
    ts("Mémoire par conteneur", [('container_memory_working_set_bytes{name!=""}', "{{name}}")], "bytes", 12, 22, stack=True, minv=0),
    ts("Réseau reçu par conteneur", [('sum by (name) (rate(container_network_receive_bytes_total{name!=""}[5m]))', "{{name}}")], "Bps", 0, 30),
    ts("Réseau émis par conteneur", [('sum by (name) (rate(container_network_transmit_bytes_total{name!=""}[5m]))', "{{name}}")], "Bps", 12, 30),
]
panels.append(row("Disponibilité des services", 38))
panels += [
    ts("Cibles Prometheus (1 = joignable)", [('up', "{{job}}")], "short", 0, 39, w=12, h=7, minv=0, maxv=1.2),
    ts("Sites web (sonde blackbox, 1 = en ligne)", [('probe_success', "{{instance}}")], "short", 12, 39, w=12, h=7, minv=0, maxv=1.2),
]

dash = {
    "__inputs": [{"name": "DS_PROMETHEUS", "label": "Prometheus", "description": "", "type": "datasource",
                  "pluginId": "prometheus", "pluginName": "Prometheus"}],
    "__requires": [{"type": "grafana", "id": "grafana", "name": "Grafana", "version": "10.0.0"},
                   {"type": "datasource", "id": "prometheus", "name": "Prometheus", "version": "1.0.0"}],
    "uid": "thetiptop-infra",
    "title": "thetiptop — Infrastructure (CPU, RAM, disque, réseau, conteneurs)",
    "tags": ["thetiptop", "infrastructure"],
    "timezone": "browser",
    "schemaVersion": 39,
    "version": 1,
    "refresh": "30s",
    "time": {"from": "now-6h", "to": "now"},
    "editable": True,
    "graphTooltip": 1,
    "panels": panels,
    "templating": {"list": []},
    "annotations": {"list": []},
}
with open("monitoring/grafana-thetiptop-infra.json", "w", encoding="utf-8") as f:
    json.dump(dash, f, ensure_ascii=False, indent=2)
print("panels:", len(panels))
