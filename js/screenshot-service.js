/**
 * BRIGHTSHELL - Screenshot Service
 * Service pour générer des captures d'écran de sites web en 1920x1080
 * Met en cache les captures pendant 24 heures
 */

class ScreenshotService {
    constructor() {
        // Configuration
        this.cacheDuration = 24 * 60 * 60 * 1000; // 24 heures en millisecondes
        this.cachePrefix = 'brightshell_screenshot_';
        this.defaultWidth = 1920;
        this.defaultHeight = 1080;
    }

    /**
     * Vérifie si une capture est en cache et toujours valide
     * @param {string} url - L'URL du site
     * @returns {Object|null} Les données du cache ou null
     */
    getFromCache(url) {
        try {
            const cacheKey = this.cachePrefix + btoa(url).replace(/[/+=]/g, '');
            const cached = localStorage.getItem(cacheKey);
            
            if (!cached) return null;

            const cacheData = JSON.parse(cached);
            const now = Date.now();

            // Vérifier si le cache est encore valide (24h)
            if (now - cacheData.timestamp < this.cacheDuration) {
                return cacheData;
            } else {
                // Cache expiré, le supprimer
                localStorage.removeItem(cacheKey);
                return null;
            }
        } catch (error) {
            console.error('Erreur lors de la lecture du cache:', error);
            return null;
        }
    }

    /**
     * Stocke une capture dans le cache
     * @param {string} url - L'URL du site
     * @param {string} screenshotUrl - L'URL de l'image de capture
     */
    setCache(url, screenshotUrl) {
        try {
            const cacheKey = this.cachePrefix + btoa(url).replace(/[/+=]/g, '');
            const cacheData = {
                url: screenshotUrl,
                timestamp: Date.now()
            };
            localStorage.setItem(cacheKey, JSON.stringify(cacheData));
        } catch (error) {
            console.error('Erreur lors de la sauvegarde du cache:', error);
            // Si le localStorage est plein, essayer de nettoyer les anciens caches
            this.cleanOldCache();
        }
    }

    /**
     * Nettoie les caches expirés
     */
    cleanOldCache() {
        try {
            const now = Date.now();
            for (let i = localStorage.length - 1; i >= 0; i--) {
                const key = localStorage.key(i);
                if (key && key.startsWith(this.cachePrefix)) {
                    try {
                        const cacheData = JSON.parse(localStorage.getItem(key));
                        if (now - cacheData.timestamp >= this.cacheDuration) {
                            localStorage.removeItem(key);
                        }
                    } catch (e) {
                        // Données corrompues, les supprimer
                        localStorage.removeItem(key);
                    }
                }
            }
        } catch (error) {
            console.error('Erreur lors du nettoyage du cache:', error);
        }
    }

    /**
     * Liste des services de capture disponibles (ordre de préférence)
     * @param {string} url - L'URL du site à capturer
     * @param {number} width - Largeur de la capture
     * @param {number} height - Hauteur de la capture
     * @returns {Array} Liste des services disponibles
     */
    getScreenshotServices(url, width, height) {
        return [
            {
                name: 'mini.s-shot.ru',
                url: `https://mini.s-shot.ru/1024x768/JPEG/${width}/${height}/?${encodeURIComponent(url)}`,
                free: true
            },
            {
                name: 'api.screenshot.rocks',
                url: `https://api.screenshot.rocks/?url=${encodeURIComponent(url)}&width=${width}&height=${height}&format=png`,
                free: true
            },
            {
                name: 'thumbnail.ws',
                url: `https://api.thumbnail.ws/api/ab5d47a83e6c0b73291eb96c8c4f9d87e6c0c589fd8e/thumbnail/get?url=${encodeURIComponent(url)}&width=${width}`,
                free: true
            },
            {
                name: 'screenshot.website',
                url: `https://screenshot.website/${encodeURIComponent(url)}?width=${width}&height=${height}`,
                free: true
            }
        ];
    }

    /**
     * Génère une capture d'écran d'une URL (1920x1080)
     * Vérifie d'abord le cache
     * @param {string} url - L'URL du site à capturer
     * @param {Object} options - Options de configuration
     * @returns {Promise<string>} URL de l'image de capture
     */
    async getScreenshot(url, options = {}) {
        // Vérifier le cache d'abord
        const cached = this.getFromCache(url);
        if (cached && cached.url && !cached.url.startsWith('data:image')) {
            // Cache valide (mais pas une data URL, donc c'est une URL externe)
            console.log(`[Screenshot] ✅ Capture récupérée du cache pour ${url}`);
            console.log(`[Screenshot] 🔗 URL en cache: ${cached.url}`);
            return cached.url;
        }
        
        console.log(`[Screenshot] 🔄 Génération d'une nouvelle capture pour ${url} (1920x1080)`);

        const {
            width = this.defaultWidth,
            height = this.defaultHeight
        } = options;

        // Obtenir la liste des services disponibles
        const services = this.getScreenshotServices(url, width, height);
        
        // Retourner le premier service gratuit disponible
        // Le système de fallback se fera lors du chargement si l'image échoue
        const firstService = services.find(s => s.free) || services[0];
        if (!firstService) {
            console.error(`[Screenshot] ❌ Aucun service disponible pour ${url}`);
            return null;
        }
        
        const screenshotUrl = firstService.url;
        
        console.log(`[Screenshot] 📋 Tentative avec: ${firstService.name}`);
        console.log(`[Screenshot] 🔗 URL générée: ${screenshotUrl}`);
        console.log(`[Screenshot] 💡 Si échec, ${services.length - 1} autres services seront essayés automatiquement`);
        
        // IMPORTANT: Le cache est 100% côté client (localStorage), jamais sur le serveur
        // On stocke uniquement l'URL de l'image générée par le service externe
        
        return screenshotUrl;
    }

    /**
     * Génère une capture d'écran côté client via iframe invisible + html2canvas
     * Cette méthode fonctionne uniquement si CORS le permet (sites de même origine ou avec CORS activé)
     * @param {string} url - L'URL du site à capturer
     * @param {number} width - Largeur de la capture
     * @param {number} height - Hauteur de la capture
     * @returns {Promise<string|null>} Data URL de l'image ou null si échec
     */
    async captureWithIframe(url, width, height) {
        return new Promise((resolve) => {
            // Vérifier si html2canvas est disponible
            if (typeof html2canvas === 'undefined') {
                console.log(`[Screenshot] html2canvas non disponible, passage aux services externes`);
                resolve(null);
                return;
            }

            console.log(`[Screenshot] 🎨 Tentative de capture côté client avec html2canvas pour ${url}`);
            
            // Créer une iframe invisible hors de l'écran
            const iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.left = '-9999px';
            iframe.style.top = '-9999px';
            iframe.style.width = `${width}px`;
            iframe.style.height = `${height}px`;
            iframe.style.border = 'none';
            iframe.src = url;
            
            let resolved = false;
            const timeout = setTimeout(() => {
                if (!resolved) {
                    resolved = true;
                    console.warn(`[Screenshot] ⏱️ Timeout (15s) pour la capture iframe de ${url}`);
                    iframe.remove();
                    resolve(null);
                }
            }, 15000); // 15 secondes pour charger la page
            
            iframe.onload = async () => {
                if (resolved) return;
                
                try {
                    // Attendre que le contenu soit complètement chargé
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    
                    // Vérifier si on peut accéder au contenu (CORS check)
                    let body;
                    try {
                        body = iframe.contentDocument?.body || iframe.contentWindow?.document?.body;
                    } catch (e) {
                        // Erreur CORS normale pour les iframes cross-origin
                        throw new Error('CORS: Impossible d\'accéder au contenu de l\'iframe');
                    }
                    
                    if (!body) {
                        throw new Error('Le contenu de l\'iframe est inaccessible');
                    }
                    
                    // Essayer de capturer avec html2canvas
                    const canvas = await html2canvas(body, {
                        width: width,
                        height: height,
                        scale: 1,
                        useCORS: true,
                        allowTaint: false,
                        logging: false
                    });
                    
                    const dataUrl = canvas.toDataURL('image/png');
                    console.log(`[Screenshot] ✅ Capture côté client réussie pour ${url}`);
                    
                    resolved = true;
                    clearTimeout(timeout);
                    iframe.remove();
                    resolve(dataUrl);
                    
                } catch (error) {
                    if (resolved) return;
                    resolved = true;
                    clearTimeout(timeout);
                    console.warn(`[Screenshot] ⚠️ Échec de capture html2canvas pour ${url}:`, error.message);
                    console.log(`[Screenshot] 💡 Probable limitation CORS, passage aux services externes`);
                    iframe.remove();
                    resolve(null);
                }
            };
            
            iframe.onerror = () => {
                if (resolved) return;
                resolved = true;
                clearTimeout(timeout);
                console.warn(`[Screenshot] ⚠️ Erreur de chargement de l'iframe pour ${url}`);
                iframe.remove();
                resolve(null);
            };
            
            // Ajouter l'iframe au DOM pour commencer le chargement
            document.body.appendChild(iframe);
        });
    }

    /**
     * Essaie de charger une image depuis un service donné
     * @param {string} screenshotUrl - L'URL de l'image à charger
     * @param {HTMLElement} container - Le conteneur
     * @param {string} url - L'URL originale du site
     * @param {HTMLElement} placeholder - Le placeholder
     * @returns {Promise<boolean>} true si l'image a chargé avec succès
     */
    async tryLoadImage(screenshotUrl, container, url, placeholder) {
        return new Promise((resolve) => {
            // Supprimer toutes les images existantes avant d'essayer une nouvelle
            const existingImgs = container.querySelectorAll('img');
            existingImgs.forEach(img => img.remove());
            
            const img = document.createElement('img');
            img.src = screenshotUrl;
            img.alt = `Capture d'écran de ${url}`;
            img.loading = 'lazy';
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.display = 'none'; // Cacher jusqu'à ce qu'elle charge
            
            let resolved = false;
            
            img.onload = () => {
                if (resolved) return;
                resolved = true;
                img.classList.add('loaded');
                img.style.display = 'block'; // Afficher maintenant
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
                console.log(`[Screenshot] ✅ Image chargée avec succès depuis ${screenshotUrl}`);
                console.log(`[Screenshot] 📊 Dimensions: ${img.naturalWidth}x${img.naturalHeight}`);
                container.appendChild(img);
                // Mettre en cache uniquement si l'image charge avec succès
                this.setCache(url, screenshotUrl);
                resolve(true);
            };
            
            img.onerror = () => {
                if (resolved) return;
                resolved = true;
                console.warn(`[Screenshot] ⚠️ Échec avec ${screenshotUrl}`);
                // Ne pas ajouter l'image au container si elle échoue
                img.remove();
                resolve(false);
            };
            
            // Timeout après 10 secondes
            setTimeout(() => {
                if (!resolved) {
                    resolved = true;
                    console.warn(`[Screenshot] ⏱️ Timeout après 10s pour ${screenshotUrl}`);
                    img.remove();
                    resolve(false);
                }
            }, 10000);
            
            // Ajouter l'image au DOM pour commencer le chargement
            container.appendChild(img);
        });
    }

    /**
     * Charge une image de capture dans un élément
     * Génère une capture en 1920x1080 et l'affiche (mise en cache 24h)
     * Essaie plusieurs services en cascade si le premier échoue
     * @param {HTMLElement} container - Le conteneur où afficher l'image
     * @param {string} url - L'URL du site à capturer
     */
    async loadScreenshot(container, url) {
        const placeholder = container.querySelector('.screenshot-placeholder');
        
        // Vérifier si une ancienne image existe et la supprimer
        const existingImg = container.querySelector('img');
        if (existingImg) {
            existingImg.remove();
        }
        const existingIframe = container.querySelector('iframe');
        if (existingIframe) {
            existingIframe.remove();
        }
        
        // Mettre à jour le placeholder pour indiquer le chargement
        if (placeholder) {
            try {
                const domain = new URL(url).hostname;
                placeholder.innerHTML = `<span>Génération de la capture...</span><br><span style="font-size: 0.7rem; opacity: 0.5; margin-top: 0.5rem; display: block;">${domain}</span>`;
                placeholder.style.display = 'block';
            } catch (e) {
                placeholder.innerHTML = '<span>Chargement...</span>';
            }
        }
        
        try {
            // Essayer d'abord le cache (pour voir si on a déjà une image valide)
            const cached = this.getFromCache(url);
            if (cached && cached.url && cached.url.startsWith('data:image')) {
                // Si le cache contient une data URL (capture côté client), l'utiliser
                console.log(`[Screenshot] ✅ Capture côté client récupérée du cache pour ${url}`);
                const img = document.createElement('img');
                img.src = cached.url;
                img.alt = `Capture d'écran de ${url}`;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                img.classList.add('loaded');
                if (placeholder) placeholder.style.display = 'none';
                container.appendChild(img);
                return; // Succès avec le cache
            }
            
            // Étape 1: Essayer html2canvas (capture côté client)
            console.log(`[Screenshot] 🎯 Étape 1/2: Tentative de capture html2canvas pour ${url}`);
            const clientCapture = await this.captureWithIframe(url, this.defaultWidth, this.defaultHeight);
            
            if (clientCapture) {
                // Succès avec html2canvas ! Mettre en cache la data URL
                this.setCache(url, clientCapture);
                
                const img = document.createElement('img');
                img.src = clientCapture;
                img.alt = `Capture d'écran de ${url}`;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                img.classList.add('loaded');
                if (placeholder) placeholder.style.display = 'none';
                container.appendChild(img);
                console.log(`[Screenshot] ✅ Capture html2canvas réussie pour ${url}`);
                return; // Succès avec html2canvas
            }
            
            // Étape 2: Si html2canvas échoue (CORS), utiliser les services externes
            console.log(`[Screenshot] 🔄 Étape 2/2: Capture côté client échouée, passage aux services externes pour ${url}`);
            
            // Essayer d'abord le cache (pour les URLs de services externes)
            let screenshotUrl = await this.getScreenshot(url, {
                width: this.defaultWidth,
                height: this.defaultHeight
            });
            
            if (!screenshotUrl) {
                throw new Error('Aucune URL générée depuis le cache');
            }
            
            // Essayer de charger l'image du cache ou du premier service
            let success = await this.tryLoadImage(screenshotUrl, container, url, placeholder);
            
            // Si échec, essayer les autres services en cascade
            if (!success) {
                console.log(`[Screenshot] 🔄 Le premier service a échoué, essai des services alternatifs...`);
                
                // Supprimer l'URL invalide du cache
                try {
                    const cacheKey = this.cachePrefix + btoa(url).replace(/[/+=]/g, '');
                    localStorage.removeItem(cacheKey);
                    console.log(`[Screenshot] 🗑️ Cache invalide supprimé pour ${url}`);
                } catch (e) {
                    console.error('[Screenshot] Erreur lors de la suppression du cache:', e);
                }
                
                // Essayer tous les autres services disponibles
                const services = this.getScreenshotServices(url, this.defaultWidth, this.defaultHeight);
                for (const service of services) {
                    if (service.url === screenshotUrl) {
                        console.log(`[Screenshot] ⏭️ Service ${service.name} déjà essayé, passage au suivant...`);
                        continue; // Déjà essayé
                    }
                    
                    console.log(`[Screenshot] 🔄 Essai avec: ${service.name}`);
                    success = await this.tryLoadImage(service.url, container, url, placeholder);
                    
                    if (success) {
                        console.log(`[Screenshot] ✅ Service ${service.name} fonctionne pour ${url}`);
                        break; // Succès, arrêter les essais
                    } else {
                        console.log(`[Screenshot] ❌ Service ${service.name} a échoué, passage au suivant...`);
                    }
                }
            }
            
            // Si tous les services ont échoué, afficher un message d'erreur détaillé
            if (!success) {
                console.error(`[Screenshot] ❌ TOUS les services ont échoué pour ${url}`);
                console.error(`[Screenshot] 💡 Raisons possibles:`);
                console.error(`[Screenshot]    - Limitations de taux (rate limiting)`);
                console.error(`[Screenshot]    - Restrictions CORS`);
                console.error(`[Screenshot]    - Services temporairement indisponibles`);
                console.error(`[Screenshot]    - URL bloquée par les services`);
                
                if (placeholder) {
                    try {
                        const domain = new URL(url).hostname;
                        placeholder.innerHTML = `
                            <span style="display: block; margin-bottom: 0.5rem;">${domain}</span>
                            <span style="font-size: 0.7rem; opacity: 0.7; display: block; margin-bottom: 0.3rem; font-weight: 500;">
                                ⚠️ Service de capture indisponible
                            </span>
                            <span style="font-size: 0.65rem; opacity: 0.5; display: block; line-height: 1.4;">
                                Les services externes ne répondent pas actuellement.<br>
                                Cela peut être dû à des limitations de taux ou<br>
                                à une indisponibilité temporaire du service.
                            </span>`;
                    } catch (e) {
                        placeholder.innerHTML = `
                            <span>⚠️ Erreur de chargement</span><br>
                            <span style="font-size: 0.65rem; opacity: 0.6; margin-top: 0.5rem; display: block;">
                                Impossible de générer la capture d'écran.<br>
                                Voir la console pour plus de détails.
                            </span>`;
                    }
                    placeholder.style.display = 'block';
                }
            }
            
            // Supprimer les images qui n'ont pas chargé (nettoyage)
            const failedImgs = container.querySelectorAll('img:not(.loaded)');
            failedImgs.forEach(img => img.remove());
        } catch (error) {
            console.error(`[Screenshot] Erreur fatale lors du chargement de la capture pour ${url}:`, error);
            console.error(`[Screenshot] Stack trace:`, error.stack);
            
            if (placeholder) {
                try {
                    const domain = new URL(url).hostname;
                    placeholder.innerHTML = `
                        <span>${domain}</span><br>
                        <span style="font-size: 0.65rem; opacity: 0.6; margin-top: 0.5rem; display: block; line-height: 1.4;">
                            Erreur lors de la génération de la capture<br>
                            <span style="font-size: 0.6rem; opacity: 0.5;">Détails dans la console du navigateur</span>
                        </span>`;
                } catch (e) {
                    placeholder.innerHTML = `
                        <span>Erreur fatale</span><br>
                        <span style="font-size: 0.65rem; opacity: 0.6; margin-top: 0.5rem; display: block;">
                            Voir la console pour plus de détails
                        </span>`;
                }
                placeholder.style.display = 'block';
            }
        }
    }


}

// Instance globale
const screenshotService = new ScreenshotService();

// Nettoyer les anciens caches au chargement de la page
window.addEventListener('load', () => {
    screenshotService.cleanOldCache();
});
