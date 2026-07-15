# BrightShield — OAuth2 / OIDC BrightShell

BrightShield est le fournisseur d'identité (IdP) de BrightShell. Il permet à des applications tierces (Futurmeal en premier) d'authentifier des utilisateurs via OAuth2 et OpenID Connect.

## URLs

| Env | Issuer / authorize / token |
|-----|----------------------------|
| **Dev** | `http://shield.brightshell.test` |
| **Prod** | `https://shield.brightshell.fr` |

| Endpoint | Chemin |
|----------|--------|
| Discovery | `/.well-known/openid-configuration` |
| Autorisation | `/oauth/authorize` |
| Token | `/oauth/token` |
| UserInfo | `/oauth/userinfo` |
| JWKS | `/oauth/jwks` |
| API scopes (prod) | `https://api.brightshell.fr/v1/brightshield/me` |

### Prod — Futurmeal (`futurmeal.pp.ua`) ↔ BrightShell (`brightshell.fr`)

**BrightShell `.env` :**
```env
APP_URL=https://brightshell.fr
BRIGHTSHELL_ROOT_DOMAIN=brightshell.fr
SESSION_DOMAIN=.brightshell.fr
BRIGHTSHELL_SHIELD_HOST=shield.brightshell.fr
BRIGHTSHIELD_ISSUER=https://shield.brightshell.fr
BRIGHTSHIELD_FUTURMEAL_REDIRECT_URIS=https://futurmeal.pp.ua/auth/brightshield/callback
BRIGHTSHIELD_FUTURMEAL_ICON_URL=https://futurmeal.pp.ua/apple-touch-icon.png
BRIGHTSHIELD_FUTURMEAL_ICON_HOSTS=futurmeal.pp.ua,www.futurmeal.pp.ua
```

**DNS / NPM :** `shield.brightshell.fr` → même app BrightShell que les autres sous-domaines.

**Enregistrer le client (une fois, sur le serveur BrightShell) :**
```bash
php artisan brightshield:register-client futurmeal \
  --redirect=https://futurmeal.pp.ua/auth/brightshield/callback
# Copier Client ID + secret dans le .env Futurmeal
```

**Futurmeal `.env` (prod) :**
```env
APP_URL=https://futurmeal.pp.ua
BRIGHTSHIELD_BASE_URL=https://shield.brightshell.fr
# En prod Docker : laisser vide si le conteneur résout shield.brightshell.fr via DNS public
BRIGHTSHIELD_API_BASE_URL=
BRIGHTSHIELD_API_HOST_HEADER=
BRIGHTSHIELD_CLIENT_ID=...
BRIGHTSHIELD_CLIENT_SECRET=...
BRIGHTSHIELD_REDIRECT_URI=https://futurmeal.pp.ua/auth/brightshield/callback
BRIGHTSHIELD_UX_MODE=redirect
BRIGHTSHIELD_APP_ICON=https://futurmeal.pp.ua/apple-touch-icon.png
BRIGHTSHIELD_SCOPES="openid profile email"
```

En prod, **pas besoin** de `host.docker.internal` : navigateur et PHP parlent tous les deux à `https://shield.brightshell.fr` (DNS public + HTTPS).

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

## Icône de l’application (écran de consentement)

L’écran BrightShield affiche **BrightShell × logo de l’app** :
- **Desktop** : logos en grand sur la zone gauche (espace libre), panneau de consentement à droite
- **Mobile** : logos au-dessus du panneau

L’app cliente envoie son icône via la query `app_icon` sur `/oauth/authorize` (Futurmeal le fait automatiquement via Socialite).

Exemple :
```
GET https://shield.brightshell.fr/oauth/authorize?...&app_icon=https://futurmeal.fr/apple-touch-icon.png
```

Sécurité : seuls les hôtes issus des `redirect_uris` du client OAuth (ou `icon_hosts` / `icon_url` en config) sont acceptés.

## Modes UX (choisis par chaque app cliente)

BrightShield expose deux façons de terminer le flux OAuth ; **l’app cliente choisit** (pas l’utilisateur final) :

1. **`redirect`** — redirection web classique : aller sur `shield.*`, revenir sur le callback avec le `code`, échange token côté serveur. **Futurmeal utilise ce mode** (`BRIGHTSHIELD_UX_MODE=redirect`).
2. **`popup`** — fenêtrage : l’app ouvre BrightShield en popup ; à la fin la fenêtre se ferme et renvoie le résultat au parent via `postMessage` (origine vérifiée). Disponible pour d’autres clients BrightShield.

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

## Session partagée : connecté à BrightShell = connecté à BrightShield

La session BrightShell est portée par un cookie sur tout le domaine (`SESSION_DOMAIN=.brightshell.fr`, appliqué automatiquement par `BrightshellSession::applySharedCookieDomain()`). Conséquences :

- Un utilisateur connecté sur `account.*` (ou n'importe quel portail) est **déjà connecté** sur `shield.*` : aucun mot de passe redemandé lors d'une autorisation OAuth.
- S'il a déjà consenti pour l'application, l'autorisation est **instantanée** (redirection directe avec le code, sans écran).
- Un invité qui arrive sur `/oauth/authorize` est envoyé vers le login `account.*`, puis revient automatiquement à l'autorisation (URL « intended » stockée dans la session partagée).
- La déconnexion BrightShell invalide la session partout, y compris sur `shield.*`.
- Comptes non vérifiés → redirigés vers la vérification e-mail ; comptes archivés → déconnectés (middleware `EnsureBrightShieldWebUser`).

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
