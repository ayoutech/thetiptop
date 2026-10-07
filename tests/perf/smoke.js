// TEST DE PERFORMANCE (k6) — palier léger sur un environnement déployé (DEV).
// Lancement : sh ci/run-perf.sh https://thetiptop-dev.onrender.com
// Seuils : moins de 1 % d'erreurs et 95 % des requêtes sous 2 s. Un seuil dépassé fait échouer le stage (statut UNSTABLE).
import http from 'k6/http';
import { check, sleep } from 'k6';

const TARGET = __ENV.TARGET;

export const options = {
  scenarios: {
    charge_legere: { executor: 'ramping-vus', startVUs: 1,
      stages: [{ duration: '15s', target: 10 }, { duration: '30s', target: 10 }, { duration: '5s', target: 0 }] },
  },
  thresholds: {
    http_req_failed:   ['rate<0.01'],
    http_req_duration: ['p(95)<2000'],
    checks:            ['rate>0.99'],
  },
};

// Réveil de l'environnement (plan gratuit Render : démarrage à froid jusqu'à ~1 min) avant de mesurer.
export function setup() {
  for (let i = 0; i < 18; i++) {
    const r = http.get(`${TARGET}/`, { timeout: '20s' });
    if (r.status === 200) return;
    sleep(5);
  }
  throw new Error(`${TARGET} ne répond pas après 90 s`);
}

const PAGES = ['/', '/pages/connexion.php', '/pages/inscription.php', '/pages/reglement.php'];

export default function () {
  const page = PAGES[Math.floor(Math.random() * PAGES.length)];
  const res = http.get(`${TARGET}${page}`);
  check(res, { 'statut 200': (r) => r.status === 200, 'contenu présent': (r) => r.body && r.body.length > 500 });
  sleep(1);
}
