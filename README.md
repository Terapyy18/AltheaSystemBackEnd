# Althea — Backend / API

API REST de **Althea**, plateforme e-commerce **B2B de matériel médical** pour les professionnels de santé.

Cette application expose l'API consommée par le frontend Next.js (catalogue, recherche, commandes, paiement Stripe) ainsi qu'un back-office d'administration.

## Stack

- **Symfony 7.4** + **API Platform 4.3** (PHP 8.2+)
- **PostgreSQL 16**
- **JWT** (lexik) + **2FA email** pour les admins (scheb)
- **Stripe** (Checkout Sessions + Webhooks)
- **EasyAdmin 5** (back-office)
- **Dompdf** (factures), **Symfony Mailer** (emails transactionnels)

## Démarrage

```bash
composer install
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load     # données de démo (purge la base)
symfony server:start                       # http://127.0.0.1:8000
```

Le frontend consomme l'API au format JSON-LD / Hydra sur `http://127.0.0.1:8000/api`.

### Variables d'environnement (`.env`)

| Clé | Description |
|---|---|
| `DATABASE_URL` | Connexion PostgreSQL (DB `ALTHEA`) |
| `JWT_SECRET_KEY` | Clé JWT |
| `STRIPE_SECRET_KEY` / `STRIPE_WEBHOOK_SECRET` | Paiement Stripe |
| `MAILER_DSN` / `MAILER_FROM` | Envoi d'emails |
| `FRONTEND_URL` / `ADMIN_EMAIL` | URL du front, email admin |

## Liens utiles

| Ressource | URL |
|---|---|
| Documentation API (API Platform) | http://127.0.0.1:8000/api |
| Back-office (EasyAdmin) | http://127.0.0.1:8000/admin |
| Dashboard stats admin | http://127.0.0.1:8000/admin/stats |

Compte admin de démo (via fixtures) : `theodumontet.pro@gmail.com` / `demo1234`.

## Endpoints principaux

| Endpoint | Auth | Usage |
|---|---|---|
| `GET /api/products` | public | Catalogue |
| `GET /api/search` | public | Recherche serveur (facettes + tris) |
| `POST /api/login` | public | Authentification JWT |
| `POST /api/users` | public | Inscription |
| `GET /api/orders` | JWT | Historique de commandes |
| `POST /api/checkout/session` | JWT | Crée une session Stripe Checkout |
| `POST /api/webhooks/stripe` | signature Stripe | Webhooks paiement |

> L'`access_control` fonctionne en **whitelist** : les routes publiques sont listées explicitement, puis `^/api` exige une authentification.

## Webhooks Stripe (test local)

Le endpoint `POST /api/webhooks/stripe` route les événements via `StripeEventDispatcher` :

| Événement Stripe | Effet |
|---|---|
| `checkout.session.completed` | Crée la commande, décrémente le stock, envoie la confirmation + facture |
| `checkout.session.expired` | Email « reprendre votre commande » |
| `payment_intent.payment_failed` | Commande `payment_failed`, email d'échec |
| `charge.refunded` | Restaure le stock, commande `refunded`, email |

```bash
# Forward le trafic webhook vers le serveur local (copier le whsec_... dans STRIPE_WEBHOOK_SECRET)
stripe listen --forward-to localhost:8000/api/webhooks/stripe

# Déclencher un événement
stripe trigger checkout.session.completed
```

**Politique de réponse HTTP** : `400` uniquement pour une signature invalide ou un payload malformé (Stripe arrête de réessayer) ; `200` pour tout le reste, y compris les échecs métier (la raison est dans les logs).

## Liens utiles

- Frontend : voir [`../AltheaFrontEnd`](../AltheaFrontEnd)
- Guide projet détaillé : [`../CLAUDE.md`](../CLAUDE.md)
