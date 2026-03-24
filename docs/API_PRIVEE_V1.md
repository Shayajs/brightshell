# API privée Brightshell (Sanctum) — préfixe `/v1`

> **Portail documentation** : le même contenu peut être affiché dans **docs.*** (page *Référence des endpoints*, dossier *API Sanctum (v1)*) via `App\Support\Documentation\PortailApiDocumentationBodies` et les migrations `2026_03_25_100000_seed_default_documentation_content` / `2026_03_28_160000_sync_portail_documentation_api_sanctum`.

Ce document décrit l’API JSON authentifiée par **jeton personnel Laravel Sanctum**. Les routes sont enregistrées dans `routes/api-private.php` et servies sur le **sous-domaine API** (`config('brightshell.domains.api_host')`, ou `api.<racine>` par défaut).
Les jetons Sanctum sont configurés **sans expiration automatique** : ils restent valides tant qu’ils ne sont pas révoqués.

## Prérequis

1. **HTTPS** recommandé en production (même origine / CORS géré par `DeveloperApiCors`).
2. En-tête **`Authorization: Bearer <token>`** avec un jeton créé depuis le portail réglages (voir ci‑dessous).
3. Compte **non archivé** et **e-mail vérifié** (middleware `EnsureSanctumApiUser`).
4. Le middleware **`ForceJsonForApiRequests`** force `Accept: application/json` pour éviter des redirections HTML en cas d’erreur d’auth.

### Comportement de l’hôte `api.*`

Sur l’hôte réservé à l’API (`BRIGHTSHELL_API_HOST` ou `api.<racine>`) : la **vitrine** du site (`/`, `/services`, …) ne répond plus en HTML — **404 JSON** (`BlockWebVitrineOnApiHost`). Toute autre URL non couverte par une route API déclenchée déclenche un **fallback** JSON 404 (`api.fallback`).

### Création des jetons (interface web)

La page **Réglages → API** (création / révocation des jetons) est actuellement protégée par le middleware **`role.developer`**. Les utilisateurs sans ce rôle n’ont pas d’UI pour générer un token, même si certains endpoints leur seraient autorisés une fois authentifiés.

### Connexion / déconnexion par token (API)

- `POST /v1/auth/token` (public) : crée un token Bearer à partir de `email`, `password`, `device_name` (optionnel).
- `DELETE /v1/auth/token` (privé, `auth:sanctum`) : révoque le token courant (déconnexion de l’appareil).

### Autorisations métier

Après l’entrée commune (Sanctum + e-mail vérifié), chaque groupe d’endpoints applique :

- des **rôles** (ex. élève, client, collaborateur, admin) dans les contrôleurs ;
- des **policies Laravel** (ex. `ProjectPolicy`, `CompanyPolicy`, `SupportTicketPolicy`, `CollaboratorTeamPolicy`) là où c’est pertinent.

Un **403** ou **404** (selon les cas) indique un droit insuffisant ou une ressource hors périmètre.

---

## URL de base

Toutes les routes ci‑dessous sont relatives à :

`https://<api_host>/v1`

Exemple : `GET https://api.example.com/v1/me`

---

## Profil et réglages

| Méthode | Chemin | Nom de route | Description |
|--------|--------|--------------|-------------|
| GET | `/me` | `api.v1.me.show` | Profil utilisateur (+ rôles). |
| PUT | `/me` | `api.v1.me.update` | Mise à jour profil (identique au contrôleur existant). |
| GET | `/notifications` | `api.v1.notifications.show` | Préférences notification + 50 dernières notifications. |
| PATCH | `/notifications` | `api.v1.notifications.update` | `browser_notifications_enabled`. |
| POST | `/notifications/lues` | `api.v1.notifications.read-all` | Tout marquer comme lu. |
| PUT | `/securite/mot-de-passe` | `api.v1.security.password` | Changer le mot de passe (`current_password`, `password`, `password_confirmation`). |
| DELETE | `/securite/autres-sessions` | `api.v1.security.sessions.destroy-others` | Révoque les autres sessions (si `session.driver` = `database`). |

---

## Portail élève (rôle `student`)

| Méthode | Chemin | Nom de route | Description |
|--------|--------|--------------|-------------|
| GET | `/courses` | `api.v1.courses.index` | Liste des cours de l’élève. |
| GET | `/courses/{studentCourse}` | `api.v1.courses.show` | Détail d’un cours. |
| GET | `/courses/{studentCourse}/quizzes/{quiz}` | `api.v1.courses.quizzes.show` | Quiz publié (réponses sans champ `is_correct`). |
| POST | `/courses/{studentCourse}/quizzes/{quiz}/soumission` | `api.v1.courses.quizzes.submit` | Soumission ; corps : `answers.{question_id}` = `answer_id`. |
| GET | `/matieres` | `api.v1.matieres.index` | Liste des matières. |
| GET | `/matieres/{studentSubject}` | `api.v1.matieres.show` | Arborescence dossiers / fichiers. |
| GET | `/fichiers/{file}/markdown` | `api.v1.fichiers.markdown` | Markdown + HTML rendu. |
| GET | `/fichiers/{file}/telecharger` | `api.v1.fichiers.download` | Téléchargement binaire. |

---

## Documentation interne (`DocAccessResolver`)

| Méthode | Chemin | Nom de route | Description |
|--------|--------|--------------|-------------|
| GET | `/documentation/nav` | `api.v1.documentation.nav` | Arborescence des pages accessibles. |
| GET | `/documentation/pages/{path}` | `api.v1.documentation.pages.show` | Dossier ou page ; `{path}` = chemin slug (regex `.*`). |

---

## Portail client (rôle `client`)

| Méthode | Chemin | Nom de route | Description |
|--------|--------|--------------|-------------|
| GET | `/clients/societes` | `api.v1.clients.companies.index` | Sociétés liées au compte. |
| GET | `/clients/societes/{company}` | `api.v1.clients.companies.show` | Détail + factures récentes (lecture). |
| PUT | `/clients/societes/{company}` | `api.v1.clients.companies.update` | Mise à jour si `CompanyPolicy@update`. |

---

## Demandes support (utilisateur connecté)

| Méthode | Chemin | Nom de route | Description |
|--------|--------|--------------|-------------|
| GET | `/mes-demandes-support` | `api.v1.support-tickets.mine.index` | Tickets dont vous êtes l’auteur (paginé). |
| GET | `/mes-demandes-support/{ticket}` | `api.v1.support-tickets.mine.show` | Détail si `user_id` = vous. |
| POST | `/mes-demandes-support` | `api.v1.support-tickets.mine.store` | Création (catégories portail) ; **throttle 10/min**. |

---

## Portail collaborateurs

Policies `CollaboratorTeamPolicy`. Paramètre de route **`collab_team`** (binding personnalisé).

| Méthode | Chemin | Nom de route | Description |
|--------|--------|--------------|-------------|
| GET | `/collaborateurs/equipes` | `api.v1.collabs.teams.index` | Liste des équipes. |
| GET | `/collaborateurs/equipes/capacites` | `api.v1.collabs.capabilities.index` | Catalogue des capacités (IDs pour sync). |
| GET | `/collaborateurs/equipes/{collab_team}` | `api.v1.collabs.teams.show` | Détail équipe, capacités, membres. |
| PUT | `/collaborateurs/equipes/{collab_team}/permissions` | `api.v1.collabs.teams.permissions.update` | `capabilities[]` (IDs) ; `updateCapabilities`. |
| POST | `/collaborateurs/equipes/{collab_team}/membres` | `api.v1.collabs.teams.members.store` | Ajout par `email`. |
| DELETE | `/collaborateurs/equipes/{collab_team}/membres/{user}` | `api.v1.collabs.teams.members.destroy` | Retrait membre. |
| PATCH | `/collaborateurs/equipes/{collab_team}/membres/{user}/gerant` | `api.v1.collabs.teams.members.manager` | `is_team_manager`. |
| GET | `/collaborateurs/equipes/{collab_team}/messages` | `api.v1.collabs.teams.messages.poll` | Query `after_id` optionnel. |
| POST | `/collaborateurs/equipes/{collab_team}/messages` | `api.v1.collabs.teams.messages.store` | Nouveau message. |

---

## Portail projet

`{project}` = **slug** du projet (`Project` route key). Autorisations : `ProjectPolicy` (view, update, annotate, download) + logique des contrôleurs (alignée sur le portail web).

| Méthode | Chemin | Nom de route | Rappel droits |
|--------|--------|--------------|----------------|
| GET | `/projets` | `api.v1.projects.index` | Projets visibles. |
| GET | `/projets/{project}` | `api.v1.projects.show` | Détail + pivot membres. |
| GET/POST/DELETE | `/projets/{project}/notes` … | `api.v1.projects.notes.*` | Annoter / modifier. |
| GET/POST/PUT/DELETE | `/projets/{project}/demandes` … | `api.v1.projects.requests.*` | Création ouverte aux viewers ; édition `update`. |
| GET/POST/PUT/DELETE | `/projets/{project}/rendez-vous` … | `api.v1.projects.appointments.*` | Écriture `update`. |
| GET/POST/DELETE | `/projets/{project}/kanban` … | `api.v1.projects.kanban.*` | Lecture tous ; écriture `update`. |
| GET/POST/DELETE | `/projets/{project}/documents` … | `api.v1.projects.documents.*` | Upload `multipart` ; téléchargement `download` policy. |
| GET/POST/PUT/DELETE | `/projets/{project}/cahier-des-charges` … | `api.v1.projects.specs.*` | Brouillons filtrés si pas `update`. |
| GET/POST/PUT/DELETE | `/projets/{project}/contrats` … | `api.v1.projects.contracts.*` | `update`. |
| GET/POST/PUT/DELETE | `/projets/{project}/prix` … | `api.v1.projects.prices.*` | `update`. |

---

## Préfixe admin (`/v1/admin/…`)

Réservé aux comptes **`is_admin` ou rôle `admin`** (contrôle explicite dans les contrôleurs + policies pour tickets).

| Zone | Préfixe | Points d’entrée principaux |
|------|---------|----------------------------|
| Recherche | `/admin/recherche?q=` | `api.v1.admin.search` (throttle 60/min). |
| Factures | `/admin/factures` | CRUD `api.v1.admin.invoices.*` |
| Support | `/admin/demandes-support` | Liste, détail, patch statut, POST vérifier e-mail membre. |
| Membres | `/admin/membres` | Liste (query `status=active|archived|all`), détail `{member}`. |
| Sociétés | `/admin/societes` | Liste toutes les sociétés. |
| Déclarations | `/admin/declarations/entreprise`, `/urssaf` | Fiche entreprise + résumé CA URSSAF. |
| Projets | `/admin/projets` | CRUD, archiver/réactiver, membres, `meta` (companies + users). |

Noms de routes : `api.v1.admin.*` (voir `routes/api-private.php`).

---

## API publique (rappel)

Sans Sanctum, sur le même host API : voir `routes/api-public.php` et `config/brightshell-api.php` (ex. `GET /v1/entreprise`).

---

## Fichiers utiles

| Fichier | Rôle |
|---------|------|
| `routes/api-private.php` | Déclaration des routes privées. |
| `bootstrap/app.php` | Domaine API, middlewares, préfixe `v1`. |
| `app/Http/Middleware/EnsureSanctumApiUser.php` | Garde e-mail vérifié + compte actif. |
| `app/Http/Middleware/DeveloperApiCors.php` | CORS API privée. |
| `app/Http/Controllers/Api/V1/**/*.php` | Implémentation. |

---

## Ancien comportement « développeur uniquement »

Le middleware `EnsureDeveloperApiAccess` n’est **plus** appliqué sur tout le groupe privé. Il reste dans le codebase mais inutilisé ; on peut le réattacher à un sous-ensemble de routes si besoin.
