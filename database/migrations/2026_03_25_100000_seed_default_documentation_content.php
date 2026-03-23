<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doc_nodes') || ! Schema::hasTable('doc_node_role')) {
            return;
        }

        if (DB::table('doc_nodes')->whereNull('parent_id')->where('slug', 'accueil')->exists()) {
            return;
        }

        $now = now();
        $roleIds = DB::table('roles')->pluck('id', 'slug')->all();
        if ($roleIds === []) {
            return;
        }

        $allReaderIds = array_values(array_filter([
            $roleIds['admin'] ?? null,
            $roleIds['collaborator'] ?? null,
            $roleIds['client'] ?? null,
            $roleIds['student'] ?? null,
            $roleIds['developer'] ?? null,
        ]));

        if ($allReaderIds === []) {
            return;
        }

        DB::transaction(function () use ($now, $allReaderIds, $roleIds): void {
            $rootId = DB::table('doc_nodes')->insertGetId([
                'parent_id' => null,
                'slug' => 'accueil',
                'title' => 'Documentation BrightShell',
                'is_folder' => true,
                'body' => null,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($allReaderIds as $rid) {
                DB::table('doc_node_role')->insert([
                    'doc_node_id' => $rootId,
                    'role_id' => $rid,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $pages = [
                [
                    'slug' => 'bienvenue',
                    'title' => 'Bienvenue',
                    'sort_order' => 0,
                    'body' => $this->markdownBienvenue(),
                ],
                [
                    'slug' => 'roles-et-portails',
                    'title' => 'Rôles et portails',
                    'sort_order' => 10,
                    'body' => $this->markdownRolesEtPortails(),
                ],
                [
                    'slug' => 'reglages-compte',
                    'title' => 'Réglages et compte',
                    'sort_order' => 20,
                    'body' => $this->markdownReglagesCompte(),
                ],
            ];

            foreach ($pages as $p) {
                DB::table('doc_nodes')->insert([
                    'parent_id' => $rootId,
                    'slug' => $p['slug'],
                    'title' => $p['title'],
                    'is_folder' => false,
                    'body' => $p['body'],
                    'sort_order' => $p['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $developerId = $roleIds['developer'] ?? null;
            if ($developerId === null) {
                return;
            }

            $apiFolderId = DB::table('doc_nodes')->insertGetId([
                'parent_id' => $rootId,
                'slug' => 'api-developpeur',
                'title' => 'API développeur',
                'is_folder' => true,
                'body' => null,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('doc_node_role')->insert([
                'doc_node_id' => $apiFolderId,
                'role_id' => $developerId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $apiPages = [
                [
                    'slug' => 'introduction',
                    'title' => 'Introduction à l’API',
                    'sort_order' => 0,
                    'body' => $this->markdownApiIntroduction(),
                ],
                [
                    'slug' => 'reference',
                    'title' => 'Référence des endpoints',
                    'sort_order' => 10,
                    'body' => $this->markdownApiReference(),
                ],
                [
                    'slug' => 'droits-et-limites',
                    'title' => 'Droits, rôles et limites',
                    'sort_order' => 20,
                    'body' => $this->markdownApiDroits(),
                ],
                [
                    'slug' => 'jetons-et-securite',
                    'title' => 'Jetons d’accès et sécurité',
                    'sort_order' => 30,
                    'body' => $this->markdownApiJetons(),
                ],
            ];

            foreach ($apiPages as $p) {
                DB::table('doc_nodes')->insert([
                    'parent_id' => $apiFolderId,
                    'slug' => $p['slug'],
                    'title' => $p['title'],
                    'is_folder' => false,
                    'body' => $p['body'],
                    'sort_order' => $p['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('doc_nodes')) {
            return;
        }

        $root = DB::table('doc_nodes')->whereNull('parent_id')->where('slug', 'accueil')->first();
        if ($root === null) {
            return;
        }

        DB::table('doc_nodes')->where('id', $root->id)->delete();
    }

    private function markdownBienvenue(): string
    {
        return <<<'MD'
# Bienvenue

Ce portail rassemble la **documentation interne** BrightShell : navigation par rôles, réglages, et pour les comptes autorisés, la **référence API**.

- Les **administrateurs** voient tout le contenu et le gèrent depuis le portail Admin → **Documentation**.
- Les pages sans rôle spécifique sur un fichier ou dossier **héritent** des lecteurs définis sur le dossier parent.
- Le dossier **API développeur** n’est visible que si votre compte a le rôle **Développeur** (attribué par un administrateur).

Connectez-vous avec le même compte que sur les autres portails (`account.*`, `courses.*`, etc.). Le domaine du site est découpé en sous-domaines ; les cookies de session doivent partager le domaine parent (ex. `SESSION_DOMAIN=.votredomaine`) pour rester connecté partout.
MD;
    }

    private function markdownRolesEtPortails(): string
    {
        return <<<'MD'
# Rôles et portails

Chaque membre peut avoir un ou plusieurs **rôles** en base. Ils déterminent quels **portails** sont accessibles après connexion.

| Rôle (slug) | Portail principal | Usage |
|-------------|-------------------|--------|
| **Administration** (`admin`) | `admin.*` | Gestion des membres, cours, sociétés, documentation, etc. Les comptes avec le flag *administrateur* héritent en pratique des mêmes accès. |
| **Collaborateur** (`collaborator`) | `collabs.*` | Espace interne collaborateur. |
| **Client** (`client`) | `users.*` | Espace client (ex. fiches société selon configuration). |
| **Élève** (`student`) | `courses.*` | Cours, matières, fichiers, quiz publiés. |
| **Développeur** (`developer`) | *(aucun portail dédié)* | Débloque l’**API authentifiée** et l’onglet **Réglages → API** pour créer des jetons personnels. |

### Cumul des rôles

Un même utilisateur peut être à la fois **élève** et **développeur** : l’API pourra alors exposer les endpoints « cours / matières » avec les mêmes règles que sur le site (données limitées à **son** compte).

### Attribution du rôle Développeur

Seuls les **administrateurs** (portail Admin → Membres) peuvent cocher le rôle **Développeur**. Il n’est **pas** proposé à l’inscription publique.
MD;
    }

    private function markdownReglagesCompte(): string
    {
        return <<<'MD'
# Réglages et compte

Le portail **`settings.*`** (Réglages) est ouvert à tout utilisateur connecté avec e-mail vérifié.

Vous y trouverez notamment :

- **Profil** — identité, coordonnées, notes de profil, avatar.
- **Notifications** — préférences et lecture des notifications.
- **Sécurité** — mot de passe, sessions (selon configuration).
- **Compte** — archivage / suppression selon les règles du site.

### API et jetons (rôle Développeur)

Si votre compte a le rôle **Développeur**, une section **API** apparaît dans la barre latérale des Réglages. Vous pouvez :

- créer des **jetons personnels** (type Laravel Sanctum) ;
- les **révoquer** à tout moment ;
- copier le secret **une seule fois** à la création (il ne sera plus affiché en clair ensuite).

Les appels API se font sur le sous-domaine **`api.`** de la plateforme, avec l’en-tête :

```http
Authorization: Bearer VOTRE_JETON
Accept: application/json
```

L’e-mail du compte doit être **vérifié** pour utiliser l’API authentifiée.
MD;
    }

    private function markdownApiIntroduction(): string
    {
        return <<<'MD'
# Introduction à l’API authentifiée

L’API **privée** sert à intégrer BrightShell dans une application externe (script, mobile, outil interne).

## Prérequis

1. Compte avec rôle **Développeur** + e-mail vérifié.
2. **Jeton** créé depuis **Réglages → API** sur `settings.*`.
3. Requêtes vers l’hôte **`api.<votre-domaine>`** (ou la valeur configurée `BRIGHTSHELL_API_HOST`).

## Authentification

Authentification **Bearer** (Sanctum) :

```http
GET /v1/me HTTP/1.1
Host: api.example.test
Authorization: Bearer 1|xxxxxxxx
Accept: application/json
```

Une couche **CORS** dédiée autorise les méthodes nécessaires (`GET`, `PUT`, etc.) pour ce groupe de routes.

## Public vs privé

- **Public** (sans jeton) : par ex. `GET /v1/entreprise` — données métier exposées volontairement en lecture seule.
- **Privé** (avec jeton + rôle développeur) : préfixe **`/v1/`** — profil, cours, matières selon les rôles métier (voir *Droits, rôles et limites*).

Les réponses d’erreur non authentifiées renvoient du **JSON** (pas une redirection HTML vers la page de connexion).
MD;
    }

    private function markdownApiReference(): string
    {
        return <<<'MD'
# Référence des endpoints (v1)

Base URL : `https://api.<domaine>` (schéma + host selon votre déploiement).

Tous les chemins ci-dessous sont relatifs à la racine de ce host.

## Profil (`/v1/me`)

| Méthode | Chemin | Description |
|---------|--------|-------------|
| `GET` | `/v1/me` | Utilisateur courant + slugs de rôles. |
| `PUT` | `/v1/me` | Mise à jour : `first_name`, `last_name`, `email`, `phone`, `profile_notes` (même logique métier que le formulaire Réglages — pas d’upload d’avatar via ce endpoint). |

## Cours (rôle **élève** requis)

| Méthode | Chemin | Description |
|---------|--------|-------------|
| `GET` | `/v1/courses` | Liste des cours de l’utilisateur + quiz publiés. |
| `GET` | `/v1/courses/{id}` | Détail d’un cours **appartenant** à l’utilisateur (`404` sinon). |

## Matières et fichiers (rôle **élève** requis)

| Méthode | Chemin | Description |
|---------|--------|-------------|
| `GET` | `/v1/matieres` | Liste des matières (métadonnées). |
| `GET` | `/v1/matieres/{id}` | Arborescence dossiers / fichiers (fichiers visibles élève, non verrouillés, etc.). |
| `GET` | `/v1/fichiers/{id}/markdown` | Corps Markdown + HTML rendu (si fichier Markdown autorisé). |
| `GET` | `/v1/fichiers/{id}/telecharger` | Téléchargement binaire (en-têtes de fichier). |

Les contrôles (verrouillage, fichier masqué, propriétaire) sont **alignés** sur le portail `courses.*`.

## Limitation de débit

Un **throttle** est appliqué sur le groupe API privée (valeur par défaut côté application, ex. ordre de grandeur 60 requêtes / minute par IP + logique Laravel). Ajustez votre client pour gérer les `429` si vous enchaînez les appels.
MD;
    }

    private function markdownApiDroits(): string
    {
        return <<<'MD'
# Droits, rôles et limites

## Rôle Développeur

Sans le rôle **`developer`**, un jeton valide ne suffit pas : l’API privée répond **`403`** avec un message explicite.

## E-mail vérifié

Compte non vérifié → **`403`** sur l’API privée (aligné sur l’exigence `verified` du site).

## Compte archivé (soft delete)

Compte indisponible → **`403`**.

## Données « cours / matières »

Même avec le rôle Développeur, les endpoints **cours** et **matières** exigent en plus le rôle **`student`**. Sinon **`403`** (« Rôle élève requis »).

Les identifiants dans l’URL (`{id}`) sont toujours résolus dans **votre** périmètre : vous ne pouvez pas lire les cours ou fichiers d’un autre utilisateur.

## Résumé

| Condition | Effet |
|-----------|--------|
| Jeton absent ou invalide | `401` JSON |
| Pas `developer` | `403` |
| Pas e-mail vérifié | `403` |
| Endpoint élève sans rôle `student` | `403` |
| Ressource d’un autre user | `404` / `403` selon le cas |
MD;
    }

    private function markdownApiJetons(): string
    {
        return <<<'MD'
# Jetons d’accès et sécurité

## Création

1. Allez sur **`settings.*` → API** (visible seulement avec le rôle Développeur).
2. Donnez un **nom** au jeton (ex. machine, projet, CI).
3. Copiez la valeur affichée **immédiatement** : elle ne sera plus montrée en clair ensuite.

## Stockage côté client

- Traitez les jetons comme des **mots de passe** : variables d’environnement, coffre-fort, jamais dans le dépôt Git.
- En cas de fuite, **révoquez** le jeton depuis Réglages et en créez un nouveau.

## Révocation

Chaque ligne dans la liste des jetons a une action **Révoquer**. L’accès cesse dès la suppression en base.

## Bonnes pratiques

- Un jeton par application / environnement (prod vs dev).
- Principe du moindre privilège côté compte BrightShell : n’attribue **Développeur** qu’aux personnes qui en ont besoin.
- Pour aller plus loin (scopes lecture seule, etc.), une évolution possible est d’utiliser les **abilities** Sanctum — non activées dans la version actuelle par défaut.

## Documentation produit (ce site)

Les pages que vous consultez ici sont gérées dans **Admin → Documentation** : arborescence, Markdown, et **rôles lecteurs** par dossier ou page (héritage si aucun rôle n’est coché sur un nœud).
MD;
    }
};
