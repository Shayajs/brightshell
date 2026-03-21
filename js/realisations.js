/**
 * BRIGHTSHELL - Réalisations Page Logic
 * Gère les onglets, les captures d'écran et les interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Le H1 "Réalisations" est géré UNIQUEMENT par initRealisationsTitle()
    // main.js a été modifié pour ignorer les H1 avec la classe .realisations-title

    // Gestion des onglets
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.dataset.tab;

            // Retirer l'état actif de tous les onglets
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));

            // Activer l'onglet sélectionné
            button.classList.add('active');
            const targetPane = document.getElementById(`${targetTab}-tab`);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });
    });

    // Charger les captures d'écran pour les sites web
    loadWebsiteScreenshots();

    // Initialiser la prévisualisation Clipped Text
    initClippedTextPreview();

    // Gestion des boutons de copie de lien
    initCopyButtons();

    // Transformer le H1 "Réalisations" avec des paramètres personnalisés
    // Cette fonction gère TOUTE la transformation du H1 "Réalisations"
    initRealisationsTitle();
});

/**
 * Transforme le titre "Réalisations" en texte clippé SVG responsive
 * Similaire à BRIGHTSHELL, réagit aux changements de taille de fenêtre
 */
function initRealisationsTitle() {
    let currentSVG = null; // Stocker le SVG actuel pour pouvoir le remplacer au resize
    
    function createRealisationsClippedText() {
        if (typeof createClippedText === 'undefined' || typeof replaceElementWithClippedText === 'undefined') {
            setTimeout(createRealisationsClippedText, 100);
            return;
        }

        // Trouver l'élément H1 "Réalisations"
        const titleElement = document.querySelector('.realisations-title.clipped');
        if (!titleElement) {
            console.warn('Élément .realisations-title.clipped non trouvé');
            return;
        }

        // Si c'est déjà un SVG (lors d'un resize), on doit le remplacer
        const isSVG = titleElement.tagName === 'svg';
        const text = isSVG ? (titleElement.dataset.originalText || 'Réalisations') : 'Réalisations';

        // Calculer la taille de police de manière responsive comme BRIGHTSHELL
        const viewportWidth = window.innerWidth;
        // Ajuster la taille : plus petite que BRIGHTSHELL car c'est un titre de page
        const fontSize = Math.max(Math.min(viewportWidth * 0.15, 300), 120); // Min 120px, max 300px, responsive

        console.log('Réalisations - FontSize calculé:', fontSize, 'Viewport:', viewportWidth);

        // Paramètres similaires à BRIGHTSHELL mais adaptés pour un titre
        const params = {
            strokeWidth: 2,
            maxRepetitions: 10,
            startOffset: 0.5,
            maxOffset: 50,
            angle: 205,
            acceleration: 2,
            fontSize: fontSize,
            fontFamily: 'Gilroy ExtraBold',
            color: '#e8f0f8'
        };

        // Si c'est déjà un SVG, on doit le remplacer
        if (isSVG) {
            // Créer le nouveau SVG avec la nouvelle taille
            const newSVG = createClippedText(text, params);
            
            // Préserver les classes et l'ID
            if (titleElement.id) newSVG.id = titleElement.id;
            newSVG.className = titleElement.className;
            newSVG.dataset.originalText = text;
            newSVG.dataset.clipped = 'true';
            
            // Appliquer les styles responsive
            newSVG.style.width = '100%';
            newSVG.style.maxWidth = '100%';
            newSVG.style.height = 'auto';
            newSVG.style.display = 'block';
            
            // Remplacer l'ancien SVG par le nouveau
            titleElement.parentNode.replaceChild(newSVG, titleElement);
            currentSVG = newSVG;
        } else {
            // Première transformation : utiliser replaceElementWithClippedText
            const result = replaceElementWithClippedText(titleElement, params);
            if (result) {
                currentSVG = result;
                // Appliquer les styles responsive
                result.style.width = '100%';
                result.style.maxWidth = '100%';
                result.style.height = 'auto';
                result.style.display = 'block';
                console.log('Réalisations transformé avec succès');
            } else {
                console.warn('Échec de la transformation de Réalisations');
            }
        }
    }

    // Fonction pour gérer le resize avec debounce
    let resizeTimeout;
    function handleResize() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            console.log('Resize détecté, régénération de Réalisations...');
            createRealisationsClippedText();
        }, 150); // Debounce de 150ms
    }

    // Attendre que la page et la police soient chargées
    function init() {
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(() => {
                setTimeout(createRealisationsClippedText, 300);
                // Ajouter l'écouteur de resize
                window.addEventListener('resize', handleResize);
            });
        } else {
            window.addEventListener('load', function() {
                setTimeout(createRealisationsClippedText, 600);
                // Ajouter l'écouteur de resize
                window.addEventListener('resize', handleResize);
            });
        }
    }

    init();
}

/**
 * Charge les images statiques des sites web depuis le dossier "image"
 */
function loadWebsiteScreenshots() {
    // Mapping des URLs vers les noms de fichiers d'images
    const imageMap = {
        'https://allotata.fr': 'image/allotata.png',
        'https://takeoff.aeroclubmarcillacestuaire.fr': 'image/takeoff.png',
        'https://lotixam.fr': 'image/lotixam.png'
    };
    
    const screenshotContainers = document.querySelectorAll('.project-screenshot');
    
    screenshotContainers.forEach((container, index) => {
        const url = container.dataset.url;
        if (!url) return;

        const placeholder = container.querySelector('.screenshot-placeholder');
        const imagePath = imageMap[url];
        
        if (!imagePath) {
            console.warn(`[Réalisations] Aucune image trouvée pour ${url}`);
            if (placeholder) {
                try {
                    const domain = new URL(url).hostname;
                    placeholder.innerHTML = `<span>${domain}</span><br><span style="font-size: 0.7rem; opacity: 0.5; margin-top: 0.5rem; display: block;">Aperçu non disponible</span>`;
                } catch (e) {
                    placeholder.innerHTML = '<span>Chargement...</span>';
                }
            }
            return;
        }
        
        // Charger l'image statique avec un léger délai entre chaque pour éviter la surcharge
        setTimeout(() => {
            const img = document.createElement('img');
            img.src = imagePath;
            img.alt = `Aperçu de ${url}`;
            img.loading = 'lazy';
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.opacity = '0';
            img.style.transition = 'opacity 0.5s ease';
            
            img.onload = () => {
                img.classList.add('loaded');
                img.style.opacity = '1';
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
                console.log(`[Réalisations] ✅ Image chargée avec succès: ${imagePath}`);
            };
            
            img.onerror = () => {
                console.error(`[Réalisations] ❌ Erreur lors du chargement de l'image: ${imagePath}`);
                if (placeholder) {
                    try {
                        const domain = new URL(url).hostname;
                        placeholder.innerHTML = `<span>${domain}</span><br><span style="font-size: 0.7rem; opacity: 0.5; margin-top: 0.5rem; display: block;">Image non disponible</span>`;
                    } catch (e) {
                        placeholder.innerHTML = '<span>Erreur de chargement</span>';
                    }
                }
            };
            
            container.appendChild(img);
        }, index * 200); // Délai progressif : 0ms, 200ms, 400ms...
    });
}

/**
 * Initialise la prévisualisation Clipped Text
 */
function initClippedTextPreview() {
    const previewContainer = document.getElementById('clipped-preview');
    if (!previewContainer) {
        return;
    }

    // Fonction pour créer la prévisualisation
    function createPreview() {
        if (typeof createClippedText === 'undefined') {
            // Réessayer après un court délai
            setTimeout(createPreview, 100);
            return;
        }

        try {
            // Nettoyer le conteneur
            previewContainer.innerHTML = '';
            
            const svg = createClippedText('BrightShell', {
                fontSize: 120,
                fontFamily: 'Gilroy ExtraBold',
                color: '#e8f0f8',
                strokeWidth: 2,
                maxRepetitions: 6,
                startOffset: 0.5,
                maxOffset: 25,
                angle: 45,
                acceleration: 2
            });

            if (svg) {
                previewContainer.appendChild(svg);
            }
        } catch (error) {
            console.error('Erreur lors de la création de la prévisualisation:', error);
        }
    }

    // Attendre que la page soit complètement chargée
    if (document.readyState === 'complete') {
        setTimeout(createPreview, 500);
    } else {
        window.addEventListener('load', function() {
            setTimeout(createPreview, 500);
        });
    }
}

/**
 * Initialise les boutons de copie de lien
 */
function initCopyButtons() {
    const copyLinkButtons = document.querySelectorAll('.copy-link-btn');
    
    copyLinkButtons.forEach(button => {
        button.addEventListener('click', function() {
            const copyUrl = button.dataset.copyUrl;
            const card = button.closest('.personal-project-card');
            const copyArea = card.querySelector('.copy-area');
            const copyInput = card.querySelector('.copy-input');
            const copyBtn = card.querySelector('.copy-btn');

            if (copyArea && copyInput) {
                // Construire l'URL complète
                const baseUrl = window.location.origin;
                const path = window.location.pathname.split('/').slice(0, -1).join('/');
                const fullUrl = baseUrl + (path ? path + '/' : '/') + copyUrl;
                copyInput.value = fullUrl;
                
                // Afficher la zone de copie
                copyArea.style.display = 'flex';
                
                // Gérer le bouton de copie (une seule fois)
                if (copyBtn && !copyBtn.dataset.listenerAdded) {
                    copyBtn.dataset.listenerAdded = 'true';
                    copyBtn.addEventListener('click', async function() {
                        try {
                            await navigator.clipboard.writeText(fullUrl);
                            copyBtn.textContent = 'Copié !';
                            copyBtn.style.background = 'rgba(74, 111, 165, 0.3)';
                            
                            setTimeout(() => {
                                copyBtn.textContent = 'Copier';
                                copyBtn.style.background = '';
                            }, 2000);
                        } catch (error) {
                            // Fallback pour les navigateurs qui ne supportent pas l'API Clipboard
                            copyInput.select();
                            document.execCommand('copy');
                            copyBtn.textContent = 'Copié !';
                            setTimeout(() => {
                                copyBtn.textContent = 'Copier';
                            }, 2000);
                        }
                    });
                }

                // Sélectionner automatiquement le texte
                copyInput.select();
            }
        });
    });
}
