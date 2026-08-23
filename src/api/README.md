# API REST — Thé Tip Top

Cette API démontre la séparation entre la logique métier (API) et l'interface
utilisateur (WebApp), conformément au cahier des charges. Elle est conçue pour
être appelée par les caisses en magasin ou un futur site e-commerce, sans
dépendre des sessions PHP de la WebApp.

## Authentification

Toutes les requêtes nécessitent une clé API, transmise soit :
- en en-tête HTTP : `X-Api-Key: ttt_demo_key_2026`
- en paramètre d'URL : `?api_key=ttt_demo_key_2026`

En production, la clé doit être définie via la variable d'environnement `API_KEY`
et communiquée uniquement aux systèmes de caisse autorisés.

## Endpoints disponibles

### GET /api/verify-code.php?code=XXXXXXXXXX

Vérifie la validité d'un code ticket sans le marquer comme utilisé.

**Exemple :**
```bash
curl "https://thetiptop.onrender.com/api/verify-code.php?code=AB12CD34EF&api_key=ttt_demo_key_2026"
```

**Réponse (200) :**
```json
{
  "code": "AB12CD34EF",
  "valide": true,
  "utilise": false,
  "gain": "infuseur",
  "date_utilisation": null
}
```

**Réponse (404) :**
```json
{ "error": "Code introuvable", "code": "AB12CD34EF" }
```

### GET /api/stats.php

Retourne les statistiques publiques du jeu-concours.

**Exemple :**
```bash
curl "https://thetiptop.onrender.com/api/stats.php?api_key=ttt_demo_key_2026"
```

**Réponse (200) :**
```json
{
  "jeu": "Thé Tip Top — Jeu-concours 100% gagnant",
  "codes_total": 500000,
  "codes_utilises": 4,
  "codes_restants": 499996,
  "taux_utilisation": 0.0,
  "participants_tirage_final": 2,
  "genere_le": "2026-08-21T10:00:00+00:00"
}
```

## Évolution prévue

Cette API constitue une première étape. La migration complète vers une
architecture API/WebApp découplée (avec authentification JWT, endpoints
CRUD complets et documentation OpenAPI) est prévue en phase 2 du projet,
une fois le site validé en production.
