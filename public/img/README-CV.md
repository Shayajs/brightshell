# Images CV (BrightShell)

## Photo de profil

1. Place le fichier sous **`public/img/`** (recommandé) :
   - `cv.png` / `cv.webp` / `cv.jpg` (prioritaire si déclaré dans `resources/data/contact.json` → `etat_civil.photo`)
   - ou l’un des fichiers détectés automatiquement : `cv.webp`, `cv.png`, `cv.jpg`, `cv.jpeg`
2. Ancien chemin encore pris en charge : `public/image/cv.jpg`

Dans **`resources/data/contact.json`**, tu peux forcer le fichier :

```json
"etat_civil": {
  "photo": "img/cv.jpg",
  ...
}
```

Si aucune photo n’existe, le site utilise **`img/logo_silhouette.webp`** (pas de lien cassé).

## Hobbies (optionnel)

Dans **`resources/data/hobby.json`**, ajoute une clé **`image`** (chemin relatif à `public/`) :

```json
{
  "nom": "Scoutisme",
  "description": "...",
  "image": "img/hobbies/scout.webp"
}
```

Le fichier doit exister, sinon l’image n’est pas affichée.
