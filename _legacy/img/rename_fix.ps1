$files = Get-ChildItem -Path . -File

foreach ($file in $files) {
    $newName = $file.Name

    # Replace "element" patterns (accounting for bad chars)
    if ($newName -match "e.+le.+ment") {
        $newName = $newName -replace "e.+le.+ment", "element"
    }

    # Replace "etoile" patterns
    if ($newName -match "e.+toile") {
        $newName = $newName -replace "e.+toile", "etoile"
    }

    # Replace "epais" patterns
    if ($newName -match "e.+pais") {
        $newName = $newName -replace "e.+pais", "epais"
    }
    
    # Fix singular/plural inconsistency if any (based on list_dir: logo_sans_fond_contour_e╠upais vs contours)
    # Actually, let's just fix the encoding. The user might want to keep contour/contours distinction if it matters, but usually standardizing is good.
    # list_dir showed: `logo_sans_fond_contour_e╠upais.png` and `logo_sans_fond_contours_e╠upais.webp`. 
    # Let's fix the bad chars first.
    
    # Clean up double underscores if any created
    $newName = $newName -replace "__", "_"

    if ($newName -ne $file.Name) {
        Write-Host "Renaming '$($file.Name)' to '$newName'"
        Rename-Item -Path $file.FullName -NewName $newName -ErrorAction SilentlyContinue
    }
}
