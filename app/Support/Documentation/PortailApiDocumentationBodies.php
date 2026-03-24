<?php

namespace App\Support\Documentation;

/**
 * Contenu Markdown des pages « Documentation » (portail docs) et de la section API.
 * Utilisé par la migration initiale (seed) et par la migration de resynchronisation.
 */
final class PortailApiDocumentationBodies
{
    public static function bienvenue(): string
    {
        return <<<'MD'
# Bienvenue

Ce portail rassemble la **documentation interne** BrightShell : navigation par rôles, réglages, et une section **API Sanctum (v1)** pour les comptes autorisés à la lire.

- Les **administrateurs** voient tout le contenu et le gèrent depuis le portail Admin → **Documentation**.
- Les pages sans rôle spécifique sur un fichier ou dossier **héritent** des lecteurs définis sur le dossier parent.
- Le dossier **API Sanctum (v1)** est visible des rôles **Développeur** et **Administration** (lecture de la doc ; la **création de jetons** reste réservée au rôle Développeur dans Réglages → API).

Une **référence détaillée** des endpoints est aussi versionnée dans le dépôt : `docs/API_PRIVEE_V1.md` (reprise dans la page *Référence des endpoints* lorsque le fichier est présent).

Connectez-vous avec le même compte que sur les autres portails (`account.*`, `courses.*`, etc.). Le domaine du site est découpé en sous-domaines ; les cookies de session doivent partager le domaine parent (ex. `SESSION_DOMAIN=.votredomaine`) pour rester connecté partout.
MD;
    }

    public static function rolesEtPortails(): string
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
| **Développeur** (`developer`) | *(aucun portail dédié)* | Permet la **création de jetons** Sanctum dans **Réglages → API**. L’usage de l’API elle‑même dépend des **autres rôles** et des policies (ex. élève pour les cours, admin pour `/v1/admin/…`). |

### Cumul des rôles

Un même utilisateur peut être à la fois **élève** et **développeur** : il peut créer un jeton et consommer les endpoints « cours / matières » pour **son** compte.

### Attribution du rôle Développeur

Seuls les **administrateurs** (portail Admin → Membres) peuvent cocher le rôle **Développeur**. Il n’est **pas** proposé à l’inscription publique.
MD;
    }

    public static function reglagesCompte(): string
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

- créer des **jetons personnels** (Laravel Sanctum) ;
- les **révoquer** à tout moment ;
- copier le secret **une seule fois** à la création (il ne sera plus affiché en clair ensuite).

Les appels API se font sur le sous-domaine **`api.`** de la plateforme, avec l’en-tête :

```http
Authorization: Bearer VOTRE_JETON
Accept: application/json
```

L’e-mail du compte doit être **vérifié** pour utiliser l’API authentifiée (middleware `EnsureSanctumApiUser`). **Tous les rôles** peuvent utiliser les endpoints pour lesquels ils ont les droits métier — pas seulement le rôle Développeur.
MD;
    }

    public static function introduction(): string
    {
        return <<<'MD'
# Introduction à l’API authentifiée (Sanctum)

L’API **privée** sert à intégrer BrightShell dans une application externe (script, mobile, outil interne).

## Prérequis

1. **Jeton** personnel créé depuis **Réglages → API** (visible seulement avec le rôle **Développeur** — voir *Jetons d’accès et sécurité*).
2. Compte **non archivé** et **e-mail vérifié**.
3. Requêtes vers l’hôte **`api.<votre-domaine>`** (ou la valeur configurée `BRIGHTSHELL_API_HOST`).

## Authentification

Authentification **Bearer** (Sanctum) :

```http
GET /v1/me HTTP/1.1
Host: api.example.test
Authorization: Bearer 1|xxxxxxxx
Accept: application/json
```

Une couche **CORS** dédiée autorise les méthodes nécessaires pour ce groupe de routes. Le middleware **`ForceJsonForApiRequests`** évite les redirections HTML sur erreur d’auth.

## Public vs privé

- **Public** (sans jeton) : par ex. `GET /v1/entreprise` — données exposées volontairement en lecture seule (`routes/api-public.php`).
- **Privé** (avec jeton + garde Sanctum) : préfixe **`/v1/`** — profil, portails métier (élève, client, projet, collabs, admin…). Les droits **ne** sont **pas** limités au seul rôle Développeur : chaque endpoint applique rôles et **policies** Laravel.

La page **Référence des endpoints** reprend la liste complète (et le fichier `docs/API_PRIVEE_V1.md` dans le dépôt sert de source canonique).
MD;
    }

    public static function reference(): string
    {
        $path = base_path('docs/API_PRIVEE_V1.md');
        $embedded = is_readable($path)
            ? trim((string) file_get_contents($path))
            : "*Le fichier `docs/API_PRIVEE_V1.md` est introuvable sur cet environnement : ouvrez le dépôt source pour la référence complète.*\n";

        return "> **Source canonique dans le dépôt** : `docs/API_PRIVEE_V1.md`. Ci‑dessous : copie intégrée lorsque le fichier est présent sur le serveur (après déploiement).\n\n---\n\n".$embedded;
    }

    public static function droitsEtLimites(): string
    {
        return <<<'MD'
# Droits, rôles et limites

## Entrée commune API privée

Tout compte authentifié par **Sanctum** avec **e-mail vérifié** et compte **non archivé** peut appeler `/v1/…`. Le middleware `EnsureSanctumApiUser` renvoie **`403`** si ce n’est pas le cas.

> L’ancien middleware « rôle développeur obligatoire sur toute l’API » (`EnsureDeveloperApiAccess`) **n’est plus** utilisé sur le groupe privé ; il peut rester dans le code pour une réutilisation ciblée.

## Droits par endpoint

Ensuite, **chaque contrôleur** impose ses règles :

- **Élève** : cours, matières, fichiers, quiz (`student`).
- **Client** : sociétés (`client`).
- **Collaborateur** : équipes, messages, permissions (`CollaboratorTeamPolicy`, etc.).
- **Projet** : `ProjectPolicy` (view, update, annotate, download).
- **Admin** : préfixe `/v1/admin/…` réservé aux comptes **admin** (flag ou rôle `admin`).

Sans le bon rôle ou la bonne policy → **`403`** ou **`404`** (selon les cas, pour ne pas révéler l’existence d’une ressource).

## Résumé des codes utiles

| Situation | Réponse typique |
|-----------|-----------------|
| Jeton absent ou invalide | `401` JSON |
| E-mail non vérifié / compte archivé | `403` |
| Rôle ou policy insuffisant | `403` / `404` |
| Ressource hors périmètre (ex. cours d’un autre utilisateur) | `404` |

## Limitation de débit

Un **throttle** est appliqué sur le domaine API (valeur configurable côté application, ex. ~120 req/min sur le groupe privé). Certaines routes ont un throttle plus strict (ex. création de ticket support). Réagissez aux réponses **`429`** côté client.
MD;
    }

    public static function jetonsEtSecurite(): string
    {
        return <<<'MD'
# Jetons d’accès et sécurité

## Création (interface web)

1. Allez sur **`settings.*` → API** (visible **seulement** avec le rôle **Développeur**).
2. Donnez un **nom** au jeton (ex. machine, projet, CI).
3. Copiez la valeur affichée **immédiatement** : elle ne sera plus montrée en clair ensuite.

> Si un utilisateur doit consommer l’API sans être Développeur, un administrateur peut lui attribuer ce rôle **uniquement** pour créer un jeton, ou une évolution produit pourrait ouvrir la création de jetons à d’autres rôles.

## Stockage côté client

- Traitez les jetons comme des **mots de passe** : variables d’environnement, coffre-fort, jamais dans le dépôt Git.
- En cas de fuite, **révoquez** le jeton depuis Réglages et en créez un nouveau.

## Révocation

Chaque ligne dans la liste des jetons a une action **Révoquer**. L’accès cesse dès la suppression en base.

## Bonnes pratiques

- Un jeton par application / environnement (prod vs dev).
- Principe du moindre privilège côté compte BrightShell : n’attribuez **Développeur** qu’aux personnes qui doivent **gérer des secrets d’intégration**.
- Les **abilities** Sanctum (scopes) peuvent être ajoutées dans une évolution future.

## Documentation produit (ce site)

Les pages que vous consultez ici sont gérées dans **Admin → Documentation** : arborescence, Markdown, et **rôles lecteurs** par dossier ou page (héritage si aucun rôle n’est coché sur un nœud).

Référence technique détaillée : fichier **`docs/API_PRIVEE_V1.md`** dans le dépôt (également intégré à la page *Référence des endpoints* si le fichier est déployé).
MD;
    }
}
