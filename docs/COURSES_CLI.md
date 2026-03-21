# CLI `php artisan courses`

Commande unique avec 3 actions : `list`, `add`, `import-quiz`. L’élève est toujours identifié par **e-mail** (compte avec rôle `student`).

## Lister les cours

```bash
php artisan courses list eleve@example.com
```

## Ajouter un cours

```bash
php artisan courses add eleve@example.com \
  --title="Python — module 1" \
  --description="..." \
  --status=in_progress \
  --starts-at=2026-09-01 \
  --ends-at=2026-12-20 \
  --weekday=1 \
  --time-start=14:00 \
  --time-end=15:30
```

- `--weekday` : `1` = lundi … `7` = dimanche (comme dans le formulaire admin).
- Créneau : les trois options `--weekday`, `--time-start`, `--time-end` sont obligatoires ensemble ; les chevauchements avec d’autres cours du même élève sont refusés.

## Importer un quiz (JSON / sortie IA)

Fichier `quiz.json` :

```json
{
  "title": "Quiz optionnel",
  "questions": [
    {
      "question": "Capitale de la France ?",
      "answers": [
        { "text": "Lyon", "correct": false },
        { "text": "Paris", "correct": true }
      ]
    }
  ]
}
```

Règles : chaque question a exactement **une** réponse avec `"correct": true`.

```bash
php artisan courses import-quiz eleve@example.com \
  --course="Python" \
  --file=/chemin/vers/quiz.json \
  --quiz-title="Contrôle module 1"
```

`--course` : fragment du titre du cours (premier match `LIKE %fragment%`).
