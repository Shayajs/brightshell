# BrightShield — OAuth2 / OIDC BrightShell

BrightShield est le fournisseur d'identité (IdP) de BrightShell. Il permet à des applications tierces (Futurmeal en premier) d'authentifier des utilisateurs via OAuth2 et OpenID Connect.

## URLs (dev)

| Service | URL |
|---------|-----|
| Issuer / discovery | `https://shield.brightshell.test/.well-known/openid-configuration` |
| Autorisation | `https://shield.brightshell.test/oauth/authorize` |
| Token | `https://shield.brightshell.test/oauth/token` |
| UserInfo | `https://shield.brightshell.test/oauth/userinfo` |
| JWKS | `https://shield.brightshell.test/oauth/jwks` |

## Installation

```bash
composer install
php artisan migrate
php artisan passport:keys
php artisan brightshield:register-client futurmeal \
  --redirect=https://futurmeal.test/auth/brightshield/callback
```

Conservez le **Client ID** et le **Client secret** affichés pour Futurmeal.

## Scopes

Toutes les informations du compte BrightShell peuvent être partagées, scope par scope :

| Scope | Description |
|-------|-------------|
| `openid` | Identifiant stable (`sub` = ID utilisateur BrightShell) |
| `profile` | Prénom, nom, avatar |
| `email` | E-mail et statut de vérification |
| `phone` | Numéro de téléphone |
| `roles` | Rôles BrightShell (client, élève, collaborateur…) + flag admin |
| `account` | Date de création, dernières connexions, notes de profil |

Futurmeal demande par défaut : `openid profile email` (configurable via `BRIGHTSHIELD_SCOPES`).

L'écran de consentement affiche **les valeurs exactes** qui seront partagées (e-mail, nom, téléphone…) avant que l'utilisateur autorise.

## Modes de redirection (côté client)

Deux types d'intégration côté site client (ex. Futurmeal) :

1. **Redirection GET classique** (défaut) : le navigateur part sur `shield.*`, revient sur le callback avec le code, et le site fait « demi-tour » avec les données autorisées.
2. **Popup avec retour** : `GET /auth/brightshield/redirect?mode=popup` ouvre le flux dans une fenêtre popup ; à la fin, la popup renvoie le résultat au parent via `postMessage` (origine vérifiée) puis se ferme.

## Isolation des routes (sécurité)

- Les routes BrightShield vivent dans un fichier dédié [`routes/brightshield.php`](../routes/brightshield.php), montées uniquement sur l'hôte `shield.*` dans `bootstrap/app.php`.
- Tout autre chemin sur `shield.*` tombe en **404 JSON** (fallback) : impossible d'atteindre la vitrine, les portails ou l'API classique via cet hôte.
- Inversement, le middleware `block.web.on.shield.host` empêche les routes vitrine de répondre sur `shield.*`.
- Les jetons sont **cloisonnés** : un jeton Sanctum (API privée) n'est pas accepté sur les routes BrightShield, et un jeton BrightShield (Passport) ne donne accès qu'aux routes BrightShield — pas de contournement possible.

## API BrightShield sur api.brightshell.fr

Les sites clients peuvent relire les données autorisées avec leur `access_token` (Bearer) sur l'hôte API :

| Endpoint | Scope requis | Retour |
|----------|--------------|--------|
| `GET /v1/brightshield/me` | — | Toutes les données couvertes par les scopes du jeton |
| `GET /v1/brightshield/me/profil` | `profile` | Nom, prénom, avatar |
| `GET /v1/brightshield/me/email` | `email` | E-mail + vérification |
| `GET /v1/brightshield/me/telephone` | `phone` | Téléphone |
| `GET /v1/brightshield/me/roles` | `roles` | Rôles + flag admin |
| `GET /v1/brightshield/me/compte` | `account` | Infos complètes du compte |

Un scope manquant → **403** avec message explicite. Routes définies dans [`routes/brightshield-api.php`](../routes/brightshield-api.php).

## Flux OAuth (Authorization Code)

1. L'application redirige vers `/oauth/authorize` avec `client_id`, `redirect_uri`, `response_type=code`, `scope`, `state`.
2. L'utilisateur se connecte sur `account.*` si nécessaire (session partagée via `SESSION_DOMAIN`).
3. Écran de consentement BrightShield (une seule fois par application).
4. Redirection vers `redirect_uri?code=...&state=...`
5. Échange `POST /oauth/token` avec `grant_type=authorization_code`, `code`, `client_id`, `client_secret`, `redirect_uri`.
6. Réponse : `access_token`, `refresh_token`, `id_token` (si scope `openid`).
7. Optionnel : `GET /oauth/userinfo` avec `Authorization: Bearer {access_token}`.

## Configuration BrightShell

Variables `.env` :

```env
BRIGHTSHELL_SHIELD_HOST=shield.brightshell.test
BRIGHTSHIELD_ISSUER=https://shield.brightshell.test
BRIGHTSHIELD_FUTURMEAL_REDIRECT_URIS=https://futurmeal.test/auth/brightshield/callback
SESSION_DOMAIN=.brightshell.test
```

## Configuration Futurmeal (client)

```env
BRIGHTSHIELD_BASE_URL=https://shield.brightshell.test
BRIGHTSHIELD_CLIENT_ID=
BRIGHTSHIELD_CLIENT_SECRET=
BRIGHTSHIELD_REDIRECT_URI=https://futurmeal.test/auth/brightshield/callback
```

## Révocation

Les utilisateurs BrightShell peuvent révoquer l'accès d'une application depuis **Réglages → Applications connectées** (`settings.*`).

## Enregistrer un nouveau client (v1)

```bash
php artisan brightshield:register-client {cle} --redirect=https://example.test/callback
```

Ajoutez la définition dans `config/brightshield.php` sous `clients` et `client_labels`.
