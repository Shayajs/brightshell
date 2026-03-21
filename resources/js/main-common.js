// Smooth animations and interactions
import { createClippedText } from './clipped-text-common.js';

// Curseur personnalisé : rond avec un point au centre
const customCursor = document.createElement('div');
customCursor.className = 'custom-cursor';
document.body.appendChild(customCursor);

// Variables pour détecter les mouvements rapides
let lastMouseX = 0;
let lastMouseY = 0;
let lastMouseTime = Date.now();
let fastMoveStartTime = null;
let fastMoveTimer = null;
const FAST_MOVE_THRESHOLD = 100; // pixels par seconde minimum pour être considéré comme rapide
const FAST_MOVE_DURATION = 1500; // 1.5 secondes en millisecondes

document.addEventListener('mousemove', (e) => {
    const currentTime = Date.now();
    const timeDelta = currentTime - lastMouseTime;
    
    // Calculer la distance parcourue
    const distance = Math.sqrt(
        Math.pow(e.clientX - lastMouseX, 2) + 
        Math.pow(e.clientY - lastMouseY, 2)
    );
    
    // Calculer la vitesse (pixels par seconde)
    const speed = timeDelta > 0 ? (distance / timeDelta) * 1000 : 0;
    
    // Mettre à jour la position du curseur
    customCursor.style.left = e.clientX + 'px';
    customCursor.style.top = e.clientY + 'px';
    
    // Détecter les mouvements rapides
    if (speed > FAST_MOVE_THRESHOLD) {
        // Si c'est le début d'un mouvement rapide, enregistrer le temps
        if (fastMoveStartTime === null) {
            fastMoveStartTime = currentTime;
        }
        
        // Vérifier si on dépasse 3 secondes de mouvement rapide
        if (currentTime - fastMoveStartTime >= FAST_MOVE_DURATION) {
            customCursor.classList.add('fast-moving');
        }
        
        // Réinitialiser le timer de réduction
        if (fastMoveTimer) {
            clearTimeout(fastMoveTimer);
        }
    } else {
        // Mouvement lent ou arrêt - réinitialiser
        fastMoveStartTime = null;
        
        // Réduire le curseur après un court délai
        if (fastMoveTimer) {
            clearTimeout(fastMoveTimer);
        }
        fastMoveTimer = setTimeout(() => {
            customCursor.classList.remove('fast-moving');
        }, 200); // Petit délai pour éviter les clignotements
    }
    
    // Mettre à jour les valeurs pour le prochain calcul
    lastMouseX = e.clientX;
    lastMouseY = e.clientY;
    lastMouseTime = currentTime;
});

// Animation au clic
document.addEventListener('mousedown', (e) => {
    if (e.button === 0) { // Clic gauche
        customCursor.classList.add('clicking');
    } else if (e.button === 2) { // Clic droit
        customCursor.classList.add('right-clicking');
    }
});

document.addEventListener('mouseup', () => {
    customCursor.classList.remove('clicking', 'right-clicking');
});

// Menu contextuel personnalisé
const contextMenu = document.createElement('div');
contextMenu.className = 'context-menu';
contextMenu.innerHTML = `
    <div class="context-menu-item" data-action="home">
        <span>Accueil</span>
    </div>
    <div class="context-menu-separator"></div>
    <div class="context-menu-item" data-action="account">
        <span class="context-menu-account-label">Connexion · Inscription</span>
    </div>
    <div class="context-menu-item" data-action="realisations">
        <span>Réalisations</span>
    </div>
    <div class="context-menu-separator"></div>
    <div class="context-menu-item" data-action="contact">
        <span>CV</span>
    </div>
    <div class="context-menu-separator"></div>
    <div class="context-menu-item" data-action="bonus">
        <span>Bonus</span>
    </div>
`;
document.body.appendChild(contextMenu);

// Gérer l'affichage du menu contextuel
document.addEventListener('contextmenu', (e) => {
    e.preventDefault();

    const labelEl = contextMenu.querySelector('.context-menu-account-label');
    if (labelEl && document.body) {
        const authed = document.body.dataset.brightshellAuthed === '1';
        const userName = document.body.dataset.brightshellUserName || '';
        labelEl.textContent = authed && userName
            ? userName
            : 'Connexion · Inscription';
    }
    
    const x = e.clientX;
    const y = e.clientY;
    
    contextMenu.style.left = x + 'px';
    contextMenu.style.top = y + 'px';
    contextMenu.classList.add('active');
    
    // Ajuster la position si le menu dépasse de l'écran
    setTimeout(() => {
        const rect = contextMenu.getBoundingClientRect();
        if (rect.right > window.innerWidth) {
            contextMenu.style.left = (x - rect.width) + 'px';
        }
        if (rect.bottom > window.innerHeight) {
            contextMenu.style.top = (y - rect.height) + 'px';
        }
    }, 0);
});

// Fermer le menu au clic ailleurs
document.addEventListener('click', (e) => {
    if (!contextMenu.contains(e.target)) {
        contextMenu.classList.remove('active');
    }
});

// Actions du menu contextuel
contextMenu.addEventListener('click', (e) => {
    const action = e.target.closest('.context-menu-item')?.dataset.action;
    
    if (action) {
        contextMenu.classList.remove('active');
        
        switch(action) {
            case 'home':
                window.location.href = '/';
                break;
            case 'account': {
                const authed = document.body?.dataset.brightshellAuthed === '1';
                const loginUrl = document.body?.dataset.brightshellLoginUrl || '/login';
                const spaceUrl = document.body?.dataset.brightshellSpaceUrl || '/';
                window.location.href = authed ? spaceUrl : loginUrl;
                break;
            }
            case 'realisations':
                window.location.href = '/realisations';
                break;
            case 'contact':
                window.location.href = '/cv';
                break;
            case 'bonus':
                // TODO: On ira s'amuser plus tard
                console.log('Bonus - À venir !');
                break;
        }
    }
});

// Détecter les éléments interactifs (liens, boutons, etc.)
const interactiveElements = document.querySelectorAll('a, button, [role="button"], input[type="button"], input[type="submit"]');

interactiveElements.forEach(element => {
    element.addEventListener('mouseenter', () => {
        customCursor.classList.add('hovering');
    });
    
    element.addEventListener('mouseleave', () => {
        customCursor.classList.remove('hovering');
    });
});

// Observer pour les nouveaux éléments ajoutés dynamiquement
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
            if (node.nodeType === 1) {
                const newInteractiveElements = node.querySelectorAll ? node.querySelectorAll('a, button, [role="button"], input[type="button"], input[type="submit"]') : [];
                newInteractiveElements.forEach(element => {
                    element.addEventListener('mouseenter', () => {
                        customCursor.classList.add('hovering');
                    });
                    element.addEventListener('mouseleave', () => {
                        customCursor.classList.remove('hovering');
                    });
                });
                
                // Vérifier si le nœud lui-même est interactif
                if (node.matches && node.matches('a, button, [role="button"], input[type="button"], input[type="submit"]')) {
                    node.addEventListener('mouseenter', () => {
                        customCursor.classList.add('hovering');
                    });
                    node.addEventListener('mouseleave', () => {
                        customCursor.classList.remove('hovering');
                    });
                }
            }
        });
    });
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Cacher le curseur quand on sort de la page
document.addEventListener('mouseleave', () => {
    customCursor.style.opacity = '0';
});

document.addEventListener('mouseenter', () => {
    customCursor.style.opacity = '1';
});

// Cursor follow effect for decorative elements
document.addEventListener('mousemove', (e) => {
    const circles = document.querySelectorAll('.circle');
    const mouseX = e.clientX;
    const mouseY = e.clientY;

    circles.forEach((circle, index) => {
        const speed = (index + 1) * 0.5;
        const x = (mouseX * speed) / 500;
        const y = (mouseY * speed) / 500;

        circle.style.transform = `translate(${x}px, ${y}px)`;
    });
});

// Add parallax effect to logo on scroll
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const logo = document.querySelector('.logo-container');
    const brandName = document.querySelector('.brand-name');

    if (logo && brandName) {
        logo.style.transform = `translateY(${scrolled * 0.5}px)`;
        brandName.style.transform = `translateY(${scrolled * 0.3}px)`;
    }
});

// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add hover effect to brand image
const brandImage = document.querySelector('.brand-text-image');
if (brandImage) {
    brandImage.addEventListener('mouseenter', () => {
        brandImage.style.transform = 'scale(1.03)';
    });

    brandImage.addEventListener('mouseleave', () => {
        brandImage.style.transform = 'scale(1)';
    });
}

// Random star twinkling
function randomizeTwinkling() {
    const stars = document.querySelectorAll('.star');
    stars.forEach(star => {
        const randomDelay = Math.random() * 4;
        star.style.animationDelay = `${randomDelay}s`;
    });
}

randomizeTwinkling();

// Page load animation
window.addEventListener('load', () => {
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 1s ease-in-out';
        document.body.style.opacity = '1';
    }, 100);
});

// Fonction pour transformer un H1 en texte clippé
function transformH1ToClippedText(h1) {
    // Vérifier si ce H1 a déjà été transformé (pour éviter les doublons)
    if (h1.dataset.clipped === 'true') {
        return;
    }

    const text = h1.textContent.trim();
    if (!text) return;

    // Récupérer les styles du H1
    const computedStyle = window.getComputedStyle(h1);
    const fontSize = parseFloat(computedStyle.fontSize) || 200;
    const color = computedStyle.color || '#FFFFFF';

    // Créer le texte clippé avec les paramètres spécifiés
    const clippedSVG = createClippedText(text, {
        fontSize: fontSize,
        fontFamily: 'Gilroy ExtraBold',
        color: color,
        strokeWidth: 2.3,
        maxRepetitions: 21,
        startOffset: 0.5,
        maxOffset: 70,
        angle: 205,
        acceleration: 4.5
    });

    // Préserver les classes et l'ID du H1 original
    if (h1.id) clippedSVG.id = h1.id;
    if (h1.className) clippedSVG.className = h1.className;
    
    // Préserver les styles inline si présents
    if (h1.style.cssText) {
        clippedSVG.style.cssText = h1.style.cssText;
    }

    // Marquer comme transformé et remplacer
    h1.dataset.clipped = 'true';
    h1.parentNode.replaceChild(clippedSVG, h1);
}

// Fonction pour remplacer tous les H1 par du texte clippé
function replaceH1WithClippedText() {
    // Transformer tous les H1 existants SAUF ceux avec .realisations-title (géré par realisations.js)
    document.querySelectorAll('h1:not([data-clipped="true"]):not(.realisations-title)').forEach(h1 => {
        transformH1ToClippedText(h1);
    });
}

// Observer pour détecter les nouveaux H1 ajoutés dynamiquement
function setupH1Observer() {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                // Vérifier si le nœud ajouté est un H1
                if (node.nodeType === 1 && node.tagName === 'H1') {
                    transformH1ToClippedText(node);
                }
                // Vérifier aussi les H1 à l'intérieur du nœud ajouté SAUF .realisations-title
                if (node.nodeType === 1 && node.querySelectorAll) {
                    node.querySelectorAll('h1:not([data-clipped="true"]):not(.realisations-title)').forEach(h1 => {
                        transformH1ToClippedText(h1);
                    });
                }
            });
        });
    });

    // Observer les changements dans le body
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

// Remplacer l'image BRIGHTSHELL par le texte clippé
function replaceBrandNameWithClippedText() {
    console.log('replaceBrandNameWithClippedText appelée');

    const brandNameContainer = document.getElementById('brand-name-clipped');
    if (!brandNameContainer) {
        console.error('brand-name-clipped non trouvé');
        return;
    }

    console.log('Conteneur trouvé, création du texte clippé...');
    
    // S'assurer que le conteneur prend toute la largeur et centre verticalement
    brandNameContainer.style.display = 'flex';
    brandNameContainer.style.alignItems = 'center';
    brandNameContainer.style.justifyContent = 'center';
    brandNameContainer.style.width = '100%';

    // Récupérer les styles pour déterminer la taille
    // On va utiliser une taille responsive basée sur la largeur de l'écran
    const viewportWidth = window.innerWidth;
    // Taille beaucoup plus grande
    const fontSize = Math.max(Math.min(viewportWidth * 0.25, 400), 250); // Min 250px, max 400px, responsive

    console.log('FontSize calculé:', fontSize);

    try {
        // Créer le texte clippé BRIGHTSHELL
        const clippedSVG = createClippedText('BRIGHTSHELL', {
            fontSize: fontSize,
            fontFamily: 'Gilroy ExtraBold',
            color: '#e8f0f8', // Couleur du texte depuis CSS
            strokeWidth: 2.3,
            maxRepetitions: 21,
            startOffset: 0.5,
            maxOffset: 70,
            angle: 205,
            acceleration: 4.5
        });

        console.log('SVG créé:', clippedSVG);

        // Appliquer les styles pour que le SVG soit responsive et grand
        clippedSVG.style.width = '100%';
        clippedSVG.style.maxWidth = '1400px';
        clippedSVG.style.height = 'auto';
        clippedSVG.style.display = 'block';
        clippedSVG.style.margin = '0 auto'; // Centrer horizontalement
        clippedSVG.style.filter = 'brightness(1.05) drop-shadow(0 0 30px rgba(208, 223, 240, 0.2))';
        clippedSVG.style.animation = 'brandGlow 4s ease-in-out infinite';
        clippedSVG.style.transition = 'transform 0.4s ease';

        // Ajouter l'effet hover
        clippedSVG.addEventListener('mouseenter', () => {
            clippedSVG.style.transform = 'scale(1.02)';
            clippedSVG.style.filter = 'brightness(1.1) drop-shadow(0 0 40px rgba(208, 223, 240, 0.3))';
        });

        clippedSVG.addEventListener('mouseleave', () => {
            clippedSVG.style.transform = 'scale(1)';
            clippedSVG.style.filter = 'brightness(1.05) drop-shadow(0 0 30px rgba(208, 223, 240, 0.2))';
        });

        brandNameContainer.appendChild(clippedSVG);
        console.log('SVG ajouté au conteneur');
    } catch (error) {
        console.error('Erreur lors de la création du texte clippé:', error);
    }
}

// Attendre que la police soit chargée avant d'appliquer
window.addEventListener('load', () => {
    console.log('Page chargée, attente de la police...');
    
    // Configurer l'observer pour détecter automatiquement les nouveaux H1
    setupH1Observer();
    
    // Attendre que la police soit chargée
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => {
            console.log('Police chargée, création du texte clippé...');
            setTimeout(() => {
                replaceH1WithClippedText(); // Transforme tous les H1 existants
                replaceBrandNameWithClippedText();
            }, 100);
        });
    } else {
        // Fallback si document.fonts n'est pas supporté
        setTimeout(() => {
            console.log('Fallback: création du texte clippé...');
            replaceH1WithClippedText(); // Transforme tous les H1 existants
            replaceBrandNameWithClippedText();
        }, 500);
    }
});