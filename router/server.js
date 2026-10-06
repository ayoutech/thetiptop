// Routeur blue/green : lit active_color.txt (GitHub) et relaie le trafic vers la couleur active.
const http = require('http');
const https = require('https');

const PORT = process.env.PORT || 10000;
const COLOR_URL = process.env.COLOR_URL ||
  'https://raw.githubusercontent.com/ayoutech/thetiptop/master/active_color.txt';
const TARGETS = {
  blue: process.env.BLUE_HOST || 'thetiptop.onrender.com',
  green: process.env.GREEN_HOST || 'thetiptop-green.onrender.com',
};
const CACHE_MS = 10000;

let color = 'blue';
let lastFetch = 0;

function refreshColor() {
  return new Promise((resolve) => {
    if (Date.now() - lastFetch < CACHE_MS) return resolve(color);
    https.get(COLOR_URL + (COLOR_URL.includes('?') ? '&' : '?') + 'cb=' + Date.now(), { headers: { 'Cache-Control': 'no-cache' }, timeout: 4000 }, (r) => {
      let d = '';
      r.on('data', (c) => (d += c));
      r.on('end', () => {
        const v = d.trim().toLowerCase();
        if (r.statusCode === 200 && TARGETS[v]) { color = v; lastFetch = Date.now(); }
        resolve(color);
      });
    }).on('error', () => resolve(color)).on('timeout', function () { this.destroy(); });
  });
}

const HOP = ['connection', 'keep-alive', 'transfer-encoding', 'upgrade', 'proxy-authenticate', 'proxy-authorization', 'te', 'trailer'];

http.createServer(async (req, res) => {
  const active = await refreshColor();
  if (req.url === '/router-status') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    return res.end(JSON.stringify({ active, target: TARGETS[active], checked_at: new Date(lastFetch).toISOString() }));
  }
  const target = TARGETS[active];
  const headers = { ...req.headers, host: target, 'x-forwarded-host': req.headers.host, 'x-forwarded-proto': 'https' };
  HOP.forEach((h) => delete headers[h]);
  const up = https.request({ host: target, path: req.url, method: req.method, headers, timeout: 90000 }, (r) => {
    const out = { ...r.headers, 'x-active-color': active };
    HOP.forEach((h) => delete out[h]);
    if (out.location) {
      for (const t of Object.values(TARGETS)) out.location = out.location.replace('https://' + t, 'https://' + req.headers.host);
    }
    res.writeHead(r.statusCode, out);
    r.pipe(res);
  });
  up.on('timeout', () => up.destroy());
  up.on('error', () => { if (!res.headersSent) res.writeHead(502); res.end('Service indisponible (' + active + ')'); });
  req.pipe(up);
}).listen(PORT, () => console.log('router on ' + PORT));