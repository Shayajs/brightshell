/**
 * BRIGHTSHELL - Réalisations Page Logic
 * Gère les onglets, les captures d'écran et les interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.dataset.tab;
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            button.classList.add('active');
            const targetPane = document.getElementById(`${targetTab}-tab`);
            if (targetPane) targetPane.classList.add('active');
        });
    });

    loadWebsiteScreenshots();
    initClippedTextPreview();
    initCopyButtons();
    initRealisationsTitle();
});

function initRealisationsTitle() {
    let currentSVG = null;

    function createRealisationsClippedText() {
        if (typeof createClippedText === 'undefined' || typeof replaceElementWithClippedText === 'undefined') {
            setTimeout(createRealisationsClippedText, 100);
            return;
        }

        const titleElement = document.querySelector('.realisations-title.clipped');
        if (!titleElement) return;

        const isSVG = titleElement.tagName === 'svg';
        const text = isSVG ? (titleElement.dataset.originalText || 'Réalisations') : 'Réalisations';
        const viewportWidth = window.innerWidth;
        const fontSize = Math.max(Math.min(viewportWidth * 0.15, 300), 120);

        const params = {
            strokeWidth: 2, maxRepetitions: 10, startOffset: 0.5, maxOffset: 50,
            angle: 205, acceleration: 2, fontSize, fontFamily: 'Gilroy ExtraBold', color: '#e8f0f8'
        };

        if (isSVG) {
            const newSVG = createClippedText(text, params);
            if (titleElement.id) newSVG.id = titleElement.id;
            newSVG.className = titleElement.className;
            newSVG.dataset.originalText = text;
            newSVG.dataset.clipped = 'true';
            Object.assign(newSVG.style, { width: '100%', maxWidth: '100%', height: 'auto', display: 'block' });
            titleElement.parentNode.replaceChild(newSVG, titleElement);
            currentSVG = newSVG;
        } else {
            const result = replaceElementWithClippedText(titleElement, params);
            if (result) {
                currentSVG = result;
                Object.assign(result.style, { width: '100%', maxWidth: '100%', height: 'auto', display: 'block' });
            }
        }
    }

    let resizeTimeout;
    function handleResize() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(createRealisationsClippedText, 150);
    }

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => {
            setTimeout(createRealisationsClippedText, 300);
            window.addEventListener('resize', handleResize);
        });
    } else {
        window.addEventListener('load', () => {
            setTimeout(createRealisationsClippedText, 600);
            window.addEventListener('resize', handleResize);
        });
    }
}

function loadWebsiteScreenshots() {
    const imageMap = {
        'https://allotata.fr': '/image/allotata.png',
        'https://takeoff.aeroclubmarcillacestuaire.fr': '/image/takeoff.png',
        'https://lotixam.fr': '/image/lotixam.png'
    };

    document.querySelectorAll('.project-screenshot').forEach((container, index) => {
        const url = container.dataset.url;
        if (!url) return;
        const placeholder = container.querySelector('.screenshot-placeholder');
        const imagePath = imageMap[url];

        if (!imagePath) {
            if (placeholder) {
                try {
                    placeholder.innerHTML = `<span>${new URL(url).hostname}</span><br><span style="font-size:0.7rem;opacity:0.5;">Aperçu non disponible</span>`;
                } catch (e) {
                    placeholder.innerHTML = '<span>Chargement...</span>';
                }
            }
            return;
        }

        setTimeout(() => {
            const img = document.createElement('img');
            img.src = imagePath;
            img.alt = `Aperçu de ${url}`;
            img.loading = 'lazy';
            Object.assign(img.style, { width: '100%', height: '100%', objectFit: 'cover', opacity: '0', transition: 'opacity 0.5s ease' });
            img.onload = () => {
                img.style.opacity = '1';
                if (placeholder) placeholder.style.display = 'none';
            };
            img.onerror = () => {
                if (placeholder) placeholder.innerHTML = '<span>Image non disponible</span>';
            };
            container.appendChild(img);
        }, index * 200);
    });
}

function initClippedTextPreview() {
    const previewContainer = document.getElementById('clipped-preview');
    if (!previewContainer) return;

    function createPreview() {
        if (typeof createClippedText === 'undefined') {
            setTimeout(createPreview, 100);
            return;
        }
        try {
            previewContainer.innerHTML = '';
            const svg = createClippedText('BrightShell', {
                fontSize: 120, fontFamily: 'Gilroy ExtraBold', color: '#e8f0f8',
                strokeWidth: 2, maxRepetitions: 6, startOffset: 0.5, maxOffset: 25,
                angle: 45, acceleration: 2
            });
            if (svg) previewContainer.appendChild(svg);
        } catch (e) {
            console.error('Erreur prévisualisation:', e);
        }
    }

    if (document.readyState === 'complete') setTimeout(createPreview, 500);
    else window.addEventListener('load', () => setTimeout(createPreview, 500));
}

function initCopyButtons() {
    document.querySelectorAll('.copy-link-btn').forEach(button => {
        button.addEventListener('click', function() {
            const copyUrl = button.dataset.copyUrl;
            const card = button.closest('.personal-project-card');
            const copyArea = card?.querySelector('.copy-area');
            const copyInput = card?.querySelector('.copy-input');
            const copyBtn = card?.querySelector('.copy-btn');

            if (!copyArea || !copyInput) return;

            const fullUrl = copyUrl.startsWith('http') ? copyUrl : window.location.origin + (copyUrl.startsWith('/') ? copyUrl : '/' + copyUrl);
            copyInput.value = fullUrl;
            copyArea.style.display = 'flex';

            if (copyBtn && !copyBtn.dataset.listenerAdded) {
                copyBtn.dataset.listenerAdded = 'true';
                copyBtn.addEventListener('click', async function() {
                    try {
                        await navigator.clipboard.writeText(copyInput.value);
                        copyBtn.textContent = 'Copié !';
                        setTimeout(() => { copyBtn.textContent = 'Copier'; }, 2000);
                    } catch (e) {
                        copyInput.select();
                        document.execCommand('copy');
                        copyBtn.textContent = 'Copié !';
                        setTimeout(() => { copyBtn.textContent = 'Copier'; }, 2000);
                    }
                });
            }
            copyInput.select();
        });
    });
}
