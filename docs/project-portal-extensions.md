# Extensions — portail `project.*`

Le noyau `projects` + `project_user` (quatre booléens) reste minimal. Les modules métier sont en **tables filles** (migration `2026_03_28_100000_create_project_module_tables`).

## Implémenté (portail)

- **Rendez-vous** : `project_appointments` — CRUD si droit `modifier`.
- **Notes** : `project_notes` — création si `annoter` ou `modifier` ; suppression auteur + annoter, ou tout si `modifier`.
- **Kanban** : `project_kanban_boards`, `project_kanban_columns`, `project_kanban_cards` — édition si `modifier` (tableau par défaut + 3 colonnes à la première visite).
- **Demandes** : `project_requests` + `support_ticket_id` nullable — création pour tout membre avec `voir` ; lien ticket et statut si `modifier`.
- **Documents** : `project_documents` (fichiers à la racine du projet pour l’instant) — envoi si `modifier`, téléchargement si `télécharger`.
- **Cahier des charges** : `project_spec_sections` (Markdown, brouillon / publié) — brouillons masqués aux seuls `voir`.
- **Contrats** : `project_contracts` + `signed_document_id` → `project_documents`.
- **Prix / devis** : `project_price_items` (HT, TVA, totaux affichés) — pas les factures légales.

## Facturation

- Les **factures** restent dans `invoices` + société ; ne pas confondre avec les lignes de devis projet.

## Pistes plus tard

- Dossiers / arborescence complète pour `project_documents`.
- Commentaires sur cartes kanban, pièces jointes sur demandes, notifications.
