# Gestion des templates mail

## Objectif

Ce module permet de gerer les emails Brightshell via:

- un rendu **JSON + PHP**
- un **template principal** (layout)
- des **sous-templates** metier
- une edition admin en temps reel (autosave + preview)

## Arborescence

- Migration: `database/migrations/2026_03_23_000000_create_mail_templates_table.php`
- Modele: `app/Models/MailTemplate.php`
- Services:
  - `app/Services/Mail/Template/MailTemplateRegistry.php`
  - `app/Services/Mail/Template/MailTemplateRenderer.php`
  - `app/Services/Mail/Template/MailTemplateVariables.php`
  - `app/Services/Mail/Template/RenderedMailTemplate.php`
- Templates JSON par defaut: `resources/mail-templates/*.json`
- Rendu Blade:
  - `resources/views/mail/layouts/base.blade.php`
  - `resources/views/mail/partials/block-*.blade.php`
- Admin:
  - `app/Http/Controllers/Admin/MailTemplatesController.php`
  - `resources/views/admin/mail-templates/*.blade.php`
  - routes `admin.mail-templates.*`

## Templates standards

- `auth.passwordless-login` : connexion sans mot de passe
- `auth.password-reset` : changement/reinitialisation mot de passe
- `contact.request` : demande de contact
- `reminder.generic` : rappels
- `auth.email-verification` : confirmation email
- `custom.personal` : message completement personnalise

## Cycle de rendu JSON + PHP

1. Le registre charge les defaults JSON dans `resources/mail-templates`.
2. Si un override existe en base (`mail_templates`), il est prioritaire.
3. Le renderer remplace les placeholders `{{variable}}`.
4. Le renderer produit:
   - `subject`
   - `html` (Blade)
   - `text` (fallback texte)
5. `MailGateway::sendTemplate()` envoie le resultat via SMTP.

## Utilisation (service)

Exemple d envoi d un template:

```php
$mailGateway->sendTemplate(
    key: 'auth.password-reset',
    vars: [
        'user_name' => 'Shaya',
        'action_url' => 'https://account.brightshell.fr/reset?token=...',
        'expires_at' => '23/03/2026 22:00',
    ],
    to: ['client@example.com'],
    options: [
        'cc' => [],
        'bcc' => [],
    ],
);
```

## Edition admin

- Liste: `GET admin.mail-templates.index`
- Edition: `GET admin.mail-templates.edit`
- Sauvegarde JSON: `PUT admin.mail-templates.update`
- Preview: `POST admin.mail-templates.preview`

L ecran admin propose:

- edition du `subject_template`
- edition `layout_json`, `content_json`, `variables_json`
- autosave periodique
- preview live HTML + texte
- publication (timestamp `published_at`)

## Placeholders

- Format: `{{nom_variable}}`
- Variables possibles:
  - declarees dans `variables_json`
  - passees au renderer via `vars`
- Si une variable manque, elle est remplacee par une chaine vide.

## Securite et robustesse

- Validation serveur des payloads JSON (`array`) dans le controller admin.
- Increment de version a chaque sauvegarde (`version`).
- Fallback texte pour les clients mail sans HTML.
- Active/inactive via `is_active` pour controler l envoi.

## Commandes utiles

```bash
php artisan migrate
php artisan optimize:clear
```
