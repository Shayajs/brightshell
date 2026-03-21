<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## BrightShell — dossier `js/` (URLs `/js/*`)

Les scripts à la racine (`js/*.js`) sont utilisés par les pages statiques (ex. `public/real/clipped.html` → `../js/clipped-text.js`). **Laravel ne sert que `public/`**, il faut donc un lien :

```bash
./scripts/setup-public-js.sh
# équivalent : cd public && ln -sfn ../js js
```

Sans symlink (Windows) : copier `js/` vers `public/js/` ou activer les symlinks Git.

## BrightShell — Frontend (Node / Vite)

Les commandes **`npm run build`** et **`npm run dev`** s’exécutent **dans le dossier du projet** `brightshell` (c’est bien le bon endroit). **Vite 8** exige **Node.js ≥ 20.19** (ou ≥ 22.12) — avec Node 18 tu auras l’erreur `CustomEvent is not defined` / version insuffisante.

**Option A — Mettre à jour Node (recommandé en local)**  
Avec [nvm](https://github.com/nvm-sh/nvm) ou [fnm](https://github.com/Schniz/fnm) :

```bash
nvm install 22
nvm use 22
cd ~/prog/brightshell && npm install && npm run build
```

**Option B — Build sans changer ta version de Node (Docker)**  
Depuis `brightshell` :

```bash
npm run build:docker       # npm ci + build (propre, un peu plus long)
npm run build:docker:quick # build seul (il faut déjà un node_modules cohérent, ex. après un build:docker une fois)
```

Docker doit être installé (WSL2 OK). Ça utilise l’image officielle **Node 22** ; NPM tourne **dans le conteneur**, le résultat est écrit dans ton `public/build/`.

## BrightShell — Premier administrateur (`admin:init`)

L’inscription publique **`/register`** est **désactivée par défaut** (`BRIGHTSHELL_REGISTRATION_OPEN=false`). Crée le premier compte avec :

```bash
php artisan migrate
php artisan admin:init
```

(invites interactives : e-mail, nom, mot de passe)

Ou en non-interactif :

```bash
php artisan admin:init --email=toi@example.com --name="Prénom Nom" --password='MotDePasseSolide!' --force
```

(`--force` si l’e-mail existe déjà : promotion `is_admin` + nouveau mot de passe.)

En **dev** uniquement, tu peux rouvrir `/register` avec `BRIGHTSHELL_REGISTRATION_OPEN=true` dans `.env`.

## BrightShell — Création de comptes (`admin:makeaccount`)

Commande orientée multi-rôles (sans inscription publique) :

```bash
php artisan admin:makeaccount --email=collab@example.com --name="Collab BrightShell" --password='MotDePasseSolide!' --role=collaborator
php artisan admin:makeaccount --email=multi@example.com --name="Multi Rôle" --password='MotDePasseSolide!' --role=admin,collaborator --force
```

- `--role` accepte un slug ou une liste CSV (`admin,collaborator,client,student`)
- `--force` met à jour le compte existant
- le flag `is_admin` est synchronisé quand le rôle `admin` est présent

### Rôles et redirection après connexion

Après login, Laravel envoie vers le portail du **rôle le plus prioritaire** (`admin` > `collaborator` > `client` > `élève`). Les slugs sont en base (`roles`) ; `php artisan migrate` crée les rôles et rattache les comptes `is_admin` au rôle `admin`.

**Domaine des liens** : si `BRIGHTSHELL_ROOT_DOMAIN` est vide, le domaine racine est **l’hôte de `APP_URL`** (sans `www.`), ex. `APP_URL=https://brightshell.test` → `account.brightshell.test`, `admin.brightshell.test`, etc.

- **Account** : `BRIGHTSHELL_ACCOUNT_HOST` ou `account.{root}`
- **Admin** : `BRIGHTSHELL_ADMIN_HOST` ou `admin.{root}`
- **Autres portails** : `collabs`, `users`, `courses`, `settings` (hosts `BRIGHTSHELL_*_HOST` optionnels, sinon `{sub}.{root}`)

Surcharge globale possible : `BRIGHTSHELL_POST_LOGIN_URL`.

### Session multi-sous-domaines (obligatoire)

Si tu vois **deux cookies** `*-session` / `XSRF-TOKEN` (un sur `.tondomaine` et un sur `admin.tondomaine`), la session est **cassée** : le navigateur envoie deux valeurs et Laravel ne sait plus laquelle lire.

- Mets `SESSION_DOMAIN=.tondomaine` (avec le point).
- `AppServiceProvider` force aussi `session.domain` sur `.{root}` au boot pour éviter les cookies **host-only** sur un sous-domaine.
- Après changement : supprime **tous** les cookies du site, ou change `SESSION_COOKIE` (ex. `brightshell-portal-session`) une fois.
- Évite `php artisan config:cache` avec un `.env` incohérent sur le serveur (valeurs figées).

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
