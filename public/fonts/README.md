# Fonts

## Gilroy ExtraBold

Le fichier `Gilroy-ExtraBold.otf` doit être placé dans ce dossier pour que la police soit chargée correctement.

Le fichier n'est pas versionné dans le dépôt. Vous devez l'ajouter manuellement :

1. Téléchargez ou copiez `Gilroy-ExtraBold.otf` dans ce dossier
2. Le layout `resources/views/layouts/app.blade.php` déclare le `@font-face` (URL via `asset()`), précharge la fonte et expose `window.__BRIGHTSHELL_FONT_URL` pour `clipped-text-common.js` (`FontFace`)

## Structure attendue

```
public/fonts/
  └── Gilroy-ExtraBold.otf
```

Vérification rapide : ouvrir dans le navigateur l’URL affichée par `php artisan tinker --execute="echo asset('fonts/Gilroy-ExtraBold.otf');"` — le fichier doit se télécharger (pas une page 404).
