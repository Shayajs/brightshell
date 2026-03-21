/**
 * BRIGHTSHELL - Clipped Stratified Text Generator
 * Crée un texte clippé avec des répétitions masquées par la forme de la lettre
 * Utilise SVG natif pour le masquage - Fonctionne du tonnerre !
 */

/** URL absolue ou chemin : injectée par layouts/app.blade.php, sinon fallback /fonts/… */
function resolveGilroyFontHref() {
    if (typeof window === 'undefined') {
        return '/fonts/Gilroy-ExtraBold.otf';
    }
    const u = window.__BRIGHTSHELL_FONT_URL;
    if (u && typeof u === 'string') {
        if (/^https?:\/\//i.test(u) || u.startsWith('//')) {
            return u;
        }
        return new URL(u, window.location.origin).href;
    }
    return new URL('/fonts/Gilroy-ExtraBold.otf', window.location.origin).href;
}

// Précharger la police explicitement
export function loadFont() {
    return new Promise((resolve, reject) => {
        const fontUrl = resolveGilroyFontHref();
        const font = new FontFace('Gilroy ExtraBold', `url(${fontUrl})`);
        
        font.load().then(function(loadedFont) {
            document.fonts.add(loadedFont);
            console.log('Police Gilroy ExtraBold chargée avec succès');
            resolve();
        }).catch(function(error) {
            console.error('Erreur lors du chargement de la police:', error);
            reject(error);
        });
    });
}

/**
 * Crée un texte clippé avec SVG
 * @param {string} text - Le texte à afficher
 * @param {Object} options - Options de configuration
 * @param {number} options.fontSize - Taille de la police (défaut: 200)
 * @param {string} options.fontFamily - Famille de police (défaut: 'Gilroy ExtraBold')
 * @param {string} options.color - Couleur du texte (défaut: '#FFFFFF')
 * @param {number} options.strokeWidth - Épaisseur du contour (défaut: 2)
 * @param {number} options.maxRepetitions - Nombre de répétitions (défaut: 12)
 * @param {number} options.startOffset - Offset initial en px (défaut: 0.5)
 * @param {number} options.maxOffset - Offset maximum en px (défaut: 30)
 * @param {number} options.angle - Angle d'orientation en degrés (0-360, défaut: 45)
 * @param {number} options.acceleration - Exposant d'accélération (défaut: 2 pour easeInQuad)
 * @returns {HTMLElement} L'élément SVG avec l'effet
 */
export function createClippedText(text, options = {}) {
    const fontSize = options.fontSize || 200;
    const fontFamily = options.fontFamily || 'Gilroy ExtraBold';
    const color = options.color || '#FFFFFF';
    const strokeWidth = options.strokeWidth || 2;
    const maxRepetitions = options.maxRepetitions || 12;
    const startOffset = options.startOffset || 0.5;
    const maxOffset = options.maxOffset || 30;
    const angle = options.angle !== undefined ? options.angle : 45; // Angle en degrés
    const acceleration = options.acceleration !== undefined ? options.acceleration : 2; // Exposant d'accélération

    // Fonction d'easing générique avec exposant
    // Si acceleration = 2, c'est easeInQuad (t²)
    // Si acceleration = 3, c'est easeInCubic (t³)
    // etc.
    const easing = (t) => {
        if (t === 0) return 0;
        return Math.pow(t, acceleration);
    };
    
    // Convertir l'angle en radians et calculer la direction
    const angleRad = (angle * Math.PI) / 180;
    const direction = {
        x: Math.cos(angleRad),
        y: Math.sin(angleRad)
    };

    // Calculer les offsets pour les répétitions
    const offsets = [];
    for (let i = 0; i < maxRepetitions; i++) {
        const t = i / (maxRepetitions - 1);
        const easedT = easing(t);
        const offset = startOffset + (maxOffset - startOffset) * easedT;
        offsets.push({
            x: offset * direction.x,
            y: offset * direction.y,
            opacity: 1 - (i / maxRepetitions) * 0.7
        });
    }

    // Calculer les dimensions nécessaires en fonction du texte réel
    const characters = text.split('').filter(c => c !== ' ');
    // Espacement entre les lettres (augmenté pour plus d'espace)
    const textWidth = fontSize * 0.75;
    const allChars = text.split('');
    
    // Calculer la largeur réelle du texte
    const textTotalWidth = (allChars.length - 1) * textWidth;
    
    // Calculer les dimensions du viewBox uniquement pour le texte visible
    // (sans tenir compte des grands offsets pour éviter que le SVG prenne trop de place)
    const paddingMin = fontSize * 0.2; // Padding minimal autour du texte
    const paddingSafety = textTotalWidth * 0.02; // Marge de sécurité de 2% de chaque côté
    const padding = paddingMin + paddingSafety; // Padding total combiné
    const visibleWidth = textTotalWidth + (padding * 2);
    const visibleHeight = fontSize + (paddingMin * 2);
    
    // Position de départ centrée dans le viewBox visible
    const startX = padding;
    // Centrer verticalement dans le viewBox visible
    const centerY = visibleHeight / 2;
    
    // Créer le conteneur SVG avec overflow: hidden pour cacher le débordement
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('width', visibleWidth);
    svg.setAttribute('height', visibleHeight);
    svg.setAttribute('viewBox', `0 0 ${visibleWidth} ${visibleHeight}`);
    svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
    svg.style.display = 'block';
    svg.style.overflow = 'hidden'; // Cacher tout ce qui dépasse du viewBox

    // Créer les définitions (masques) pour chaque lettre
    const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
    svg.appendChild(defs);

    // Pour chaque caractère (on utilise text.split pour garder les espaces pour le positionnement)
    let charIndex = 0;
    
    allChars.forEach((char) => {
        if (char === ' ') {
            charIndex++;
            return;
        }

        const charX = startX + (charIndex * textWidth);
        const maskId = `mask-${charIndex}-${char}`;

        // Créer le masque pour cette lettre
        const mask = document.createElementNS('http://www.w3.org/2000/svg', 'mask');
        mask.setAttribute('id', maskId);
        mask.setAttribute('maskUnits', 'userSpaceOnUse');
        mask.setAttribute('maskContentUnits', 'userSpaceOnUse');
        
        // Fond noir pour masquer tout (noir = masqué)
        const maskRect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        maskRect.setAttribute('x', charX - fontSize);
        maskRect.setAttribute('y', centerY - fontSize);
        maskRect.setAttribute('width', fontSize * 2);
        maskRect.setAttribute('height', fontSize * 2);
        maskRect.setAttribute('fill', 'black');
        mask.appendChild(maskRect);
        
        // Le texte dans le masque (blanc = visible)
        const maskText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        maskText.setAttribute('x', charX);
        maskText.setAttribute('y', centerY);
        maskText.setAttribute('font-family', fontFamily);
        maskText.setAttribute('font-weight', '800');
        maskText.setAttribute('font-size', fontSize);
        maskText.setAttribute('text-anchor', 'middle');
        maskText.setAttribute('dominant-baseline', 'middle');
        maskText.setAttribute('fill', 'white');
        maskText.textContent = char;
        
        mask.appendChild(maskText);
        defs.appendChild(mask);

        // Créer un groupe pour les répétitions masquées
        const repetitionsGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        repetitionsGroup.setAttribute('mask', `url(#${maskId})`);

        // Ajouter les répétitions (de la plus éloignée à la plus proche)
        offsets.slice().reverse().forEach((offset, i) => {
            const repetition = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            repetition.setAttribute('x', charX + offset.x);
            repetition.setAttribute('y', centerY + offset.y);
            repetition.setAttribute('font-family', fontFamily);
            repetition.setAttribute('font-weight', '800');
            repetition.setAttribute('font-size', fontSize);
            repetition.setAttribute('text-anchor', 'middle');
            repetition.setAttribute('dominant-baseline', 'middle');
            repetition.setAttribute('fill', 'none');
            repetition.setAttribute('stroke', color);
            repetition.setAttribute('stroke-width', strokeWidth);
            repetition.setAttribute('opacity', offset.opacity);
            repetition.textContent = char;
            
            repetitionsGroup.appendChild(repetition);
        });

        svg.appendChild(repetitionsGroup);

        // Créer le contour de la lettre principale (pas masqué)
        const outline = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        outline.setAttribute('x', charX);
        outline.setAttribute('y', centerY);
        outline.setAttribute('font-family', fontFamily);
        outline.setAttribute('font-weight', '800');
        outline.setAttribute('font-size', fontSize);
        outline.setAttribute('text-anchor', 'middle');
        outline.setAttribute('dominant-baseline', 'middle');
        outline.setAttribute('fill', 'none');
        outline.setAttribute('stroke', color);
        outline.setAttribute('stroke-width', strokeWidth);
        outline.textContent = char;
        
        svg.appendChild(outline);
        
        charIndex++;
    });

    return svg;
}

/**
 * Remplace un élément HTML par un texte clippé SVG
 * @param {HTMLElement|string} element - L'élément ou son ID à remplacer
 * @param {Object} options - Paramètres optionnels qui override les valeurs par défaut
 * @returns {HTMLElement|null} L'élément SVG créé ou null en cas d'erreur
 */
export function replaceElementWithClippedText(element, options = {}) {
    // Récupérer l'élément si c'est un ID
    if (typeof element === 'string') {
        element = document.getElementById(element);
    }
    
    if (!element) {
        console.warn('Élément non trouvé pour replaceElementWithClippedText');
        return null;
    }

    // Si c'est déjà un SVG transformé, extraire le texte
    let text = '';
    if (element.tagName === 'svg') {
        // Essayer d'extraire le texte depuis les éléments text du SVG
        const textNodes = element.querySelectorAll('text');
        if (textNodes.length > 0) {
            // Extraire le texte unique (les répétitions ont le même texte)
            const firstTextNode = textNodes[0];
            text = firstTextNode.textContent.trim();
        }
        // Si on ne peut pas extraire le texte, utiliser le dataset ou une valeur par défaut
        if (!text && element.dataset.originalText) {
            text = element.dataset.originalText;
        }
        if (!text) {
            console.warn('Impossible d\'extraire le texte du SVG pour replaceElementWithClippedText');
            return null;
        }
    } else {
        // Élément HTML normal
        text = element.textContent.trim();
        if (!text) {
            console.warn('Élément sans texte pour replaceElementWithClippedText');
            return null;
        }
    }

    // Récupérer les styles de l'élément
    const computedStyle = window.getComputedStyle(element);
    const fontSize = parseFloat(computedStyle.fontSize) || 200;
    const color = computedStyle.color || '#FFFFFF';

    // Paramètres par défaut (utiliser fontSize et color de l'élément)
    const defaultOptions = {
        fontSize: fontSize,
        fontFamily: 'Gilroy ExtraBold',
        color: color,
        strokeWidth: 2.3,
        maxRepetitions: 21,
        startOffset: 0.5,
        maxOffset: 70,
        angle: 205,
        acceleration: 4.5
    };

    // Fusionner les options : les options passées override les valeurs par défaut
    const finalOptions = { ...defaultOptions, ...options };

    // Créer le texte clippé avec les paramètres finaux
    const clippedSVG = createClippedText(text, finalOptions);

    // Préserver les classes et l'ID de l'élément original
    if (element.id) clippedSVG.id = element.id;
    if (element.className) {
        clippedSVG.className = element.className;
    }
    
    // Préserver les styles inline si présents
    if (element.style.cssText) {
        clippedSVG.style.cssText = element.style.cssText;
    }

    // Stocker le texte original dans le dataset
    clippedSVG.dataset.originalText = text;
    clippedSVG.dataset.clipped = 'true';

    // Remplacer l'élément (peut être un H1 ou un SVG déjà transformé)
    element.parentNode.replaceChild(clippedSVG, element);

    return clippedSVG;
}

// Attendre que la police soit chargée
window.addEventListener('load', function () {
    loadFont()
        .then(function () {
            console.log('Clipped Text prêt');
        })
        .catch(function () {
            console.warn('Erreur chargement police');
        });
});

// Compat : démos statiques (public/js/clipped-text.js) et intégrations tierces
if (typeof window !== 'undefined') {
    window.createClippedText = createClippedText;
    window.replaceElementWithClippedText = replaceElementWithClippedText;
}

