/**
 * BRIGHTSHELL - CV & Contact Page Logic
 * Charge et affiche les données JSON du CV
 */

// Charger toutes les données JSON
async function loadCVData() {
    try {
        const [experience, diplomes, hobby, competences, contact, certifications, references] = await Promise.all([
            fetch('data/experience.json').then(res => res.json()),
            fetch('data/diplomes.json').then(res => res.json()),
            fetch('data/hobby.json').then(res => res.json()),
            fetch('data/competences.json').then(res => res.json()),
            fetch('data/contact.json').then(res => res.json()),
            fetch('data/certifications.json').then(res => res.json()),
            fetch('data/references.json').then(res => res.json())
        ]);

        return { experience, diplomes, hobby, competences, contact, certifications, references };
    } catch (error) {
        console.error('Erreur lors du chargement des données JSON:', error);
        return null;
    }
}

// Rendre la timeline des expériences
function renderExperience(experiences) {
    const container = document.getElementById('experience-timeline');
    if (!container || !experiences || experiences.length === 0) return;

    container.innerHTML = '';

    const INITIAL_DISPLAY = 3;
    const hasMore = experiences.length > INITIAL_DISPLAY;

    experiences.forEach((exp, index) => {
        const item = document.createElement('div');
        item.className = `timeline-item ${index >= INITIAL_DISPLAY ? 'timeline-item-hidden' : ''}`;

        const dateStr = exp.date_fin && exp.date_fin.toLowerCase() === 'présent' 
            ? `${exp.date_debut} - Présent`
            : exp.date_debut && exp.date_fin
            ? `${exp.date_debut} - ${exp.date_fin}`
            : exp.date_debut || '';

        let html = `
            <div class="timeline-date">${dateStr}</div>
            <div class="timeline-title">${exp.poste}</div>
            <div class="timeline-subtitle">${exp.entreprise}${exp.lieu ? ` - ${exp.lieu}` : ''}</div>
        `;

        if (exp.description) {
            html += `<div class="timeline-description">${exp.description}</div>`;
        }

        if (exp.realisations && exp.realisations.length > 0) {
            html += '<ul class="timeline-realisations">';
            exp.realisations.forEach(real => {
                html += `<li class="timeline-realisation-item">${real}</li>`;
            });
            html += '</ul>';
        }

        if (exp.technologies && exp.technologies.length > 0) {
            html += '<div class="timeline-technologies">';
            exp.technologies.forEach(tech => {
                html += `<span class="timeline-tech-tag" data-tech-name="${tech}">${tech}</span>`;
            });
            html += '</div>';
        }

        item.innerHTML = html;
        container.appendChild(item);
    });

    // Ajouter le bouton "Voir plus / Voir moins" si nécessaire
    if (hasMore) {
        const toggleButton = document.createElement('button');
        toggleButton.className = 'timeline-toggle-btn';
        toggleButton.textContent = `Voir plus (${experiences.length - INITIAL_DISPLAY})`;
        toggleButton.setAttribute('aria-expanded', 'false');
        
        // Stocker une référence à tous les items du container
        const allItems = container.querySelectorAll('.timeline-item');
        
        toggleButton.addEventListener('click', () => {
            const isExpanded = toggleButton.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                // Réduire : cacher les éléments après INITIAL_DISPLAY
                allItems.forEach((item, index) => {
                    if (index >= INITIAL_DISPLAY) {
                        item.classList.add('timeline-item-hidden');
                    }
                });
                toggleButton.textContent = `Voir plus (${experiences.length - INITIAL_DISPLAY})`;
                toggleButton.setAttribute('aria-expanded', 'false');
            } else {
                // Développer : montrer tous les éléments
                allItems.forEach(item => {
                    item.classList.remove('timeline-item-hidden');
                });
                toggleButton.textContent = 'Voir moins';
                toggleButton.setAttribute('aria-expanded', 'true');
            }
        });
        
        container.appendChild(toggleButton);
    }
    
    // Initialiser la navigation depuis les tags vers les compétences
    initTechTagNavigation();
}

// Rendre la timeline des diplômes
function renderDiplomes(diplomes) {
    const container = document.getElementById('diplomes-timeline');
    if (!container || !diplomes || diplomes.length === 0) return;

    container.innerHTML = '';

    const INITIAL_DISPLAY = 3;
    const hasMore = diplomes.length > INITIAL_DISPLAY;

    diplomes.forEach((diplome, index) => {
        const item = document.createElement('div');
        item.className = `timeline-item ${index >= INITIAL_DISPLAY ? 'timeline-item-hidden' : ''}`;

        let html = `
            <div class="timeline-date">${diplome.date}</div>
            <div class="timeline-title">${diplome.diplome}</div>
            <div class="timeline-subtitle">${diplome.etablissement}${diplome.lieu ? ` - ${diplome.lieu}` : ''}</div>
        `;

        if (diplome.details && diplome.details.length > 0) {
            html += '<ul class="timeline-details">';
            diplome.details.forEach(detail => {
                html += `<li class="timeline-detail-item">${detail}</li>`;
            });
            html += '</ul>';
        }

        item.innerHTML = html;
        container.appendChild(item);
    });

    // Ajouter le bouton "Voir plus / Voir moins" si nécessaire
    if (hasMore) {
        const toggleButton = document.createElement('button');
        toggleButton.className = 'timeline-toggle-btn';
        toggleButton.textContent = `Voir plus (${diplomes.length - INITIAL_DISPLAY})`;
        toggleButton.setAttribute('aria-expanded', 'false');
        
        // Stocker une référence à tous les items du container
        const allItems = container.querySelectorAll('.timeline-item');
        
        toggleButton.addEventListener('click', () => {
            const isExpanded = toggleButton.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                // Réduire : cacher les éléments après INITIAL_DISPLAY
                allItems.forEach((item, index) => {
                    if (index >= INITIAL_DISPLAY) {
                        item.classList.add('timeline-item-hidden');
                    }
                });
                toggleButton.textContent = `Voir plus (${diplomes.length - INITIAL_DISPLAY})`;
                toggleButton.setAttribute('aria-expanded', 'false');
            } else {
                // Développer : montrer tous les éléments
                allItems.forEach(item => {
                    item.classList.remove('timeline-item-hidden');
                });
                toggleButton.textContent = 'Voir moins';
                toggleButton.setAttribute('aria-expanded', 'true');
            }
        });
        
        container.appendChild(toggleButton);
    }
}

// Rendre la section hobby
function renderHobby(hobbies) {
    const container = document.getElementById('hobby-grid');
    if (!container || !hobbies || hobbies.length === 0) return;

    container.innerHTML = '';

    hobbies.forEach(hobby => {
        const item = document.createElement('div');
        item.className = 'hobby-item';

        item.innerHTML = `
            <div class="hobby-name">${hobby.nom}</div>
            <div class="hobby-description">${hobby.description}</div>
        `;

        container.appendChild(item);
    });
}

// Rendre la section compétences
function renderCompetences(competences) {
    const container = document.getElementById('competences-container');
    if (!container || !competences) return;

    container.innerHTML = '';
    let competenceIdCounter = 0;

    // Langages préférés
    if (competences.langages_preferes && competences.langages_preferes.length > 0) {
        const group = document.createElement('div');
        group.className = 'competences-group';

        let html = '<div class="competences-group-title">Langages Préférés</div>';

        // Trier par priorité
        const sorted = [...competences.langages_preferes].sort((a, b) => (a.priorite || 999) - (b.priorite || 999));

        sorted.forEach(comp => {
            const competenceId = `competence-${competenceIdCounter++}`;
            const emoji = comp.emoji || '';
            const color = comp.color || '';
            html += `
                <div class="competence-item" data-competence-id="${competenceId}" data-competence-name="${comp.nom}"${emoji ? ` data-emoji="${emoji}"` : ''}${color ? ` data-color="${color}"` : ''}>
                    <div class="competence-header">
                        <span class="competence-name">${comp.nom}</span>
                        <span class="competence-level">${comp.niveau}%</span>
                    </div>
                    <div class="competence-bar">
                        <div class="competence-bar-fill" style="width: ${comp.niveau}%"></div>
                    </div>
                </div>
            `;
        });

        group.innerHTML = html;
        container.appendChild(group);
        initCompetenceEasterEggs(group);
    }

    // Langages maîtrisés
    if (competences.langages_maitrises && competences.langages_maitrises.length > 0) {
        const group = document.createElement('div');
        group.className = 'competences-group';

        let html = '<div class="competences-group-title">Langages Maîtrisés</div>';

        // Trier par priorité
        const sorted = [...competences.langages_maitrises].sort((a, b) => (a.priorite || 999) - (b.priorite || 999));

        sorted.forEach(comp => {
            const competenceId = `competence-${competenceIdCounter++}`;
            const emoji = comp.emoji || '';
            const color = comp.color || '';
            html += `
                <div class="competence-item" data-competence-id="${competenceId}" data-competence-name="${comp.nom}"${emoji ? ` data-emoji="${emoji}"` : ''}${color ? ` data-color="${color}"` : ''}>
                    <div class="competence-header">
                        <span class="competence-name">${comp.nom}</span>
                        <span class="competence-level">${comp.niveau}%</span>
                    </div>
                    <div class="competence-bar">
                        <div class="competence-bar-fill" style="width: ${comp.niveau}%"></div>
                    </div>
                </div>
            `;
        });

        group.innerHTML = html;
        container.appendChild(group);
        initCompetenceEasterEggs(group);
    }

    // Frameworks
    if (competences.frameworks && competences.frameworks.length > 0) {
        const group = document.createElement('div');
        group.className = 'competences-group';

        let html = '<div class="competences-group-title">Frameworks & Bibliothèques</div>';

        // Trier par priorité
        const sorted = [...competences.frameworks].sort((a, b) => (a.priorite || 999) - (b.priorite || 999));

        sorted.forEach(comp => {
            const competenceId = `competence-${competenceIdCounter++}`;
            const emoji = comp.emoji || '';
            const color = comp.color || '';
            html += `
                <div class="competence-item" data-competence-id="${competenceId}" data-competence-name="${comp.nom}"${emoji ? ` data-emoji="${emoji}"` : ''}${color ? ` data-color="${color}"` : ''}>
                    <div class="competence-header">
                        <span class="competence-name">${comp.nom}</span>
                        <span class="competence-level">${comp.niveau}%</span>
                    </div>
                    <div class="competence-bar">
                        <div class="competence-bar-fill" style="width: ${comp.niveau}%"></div>
                    </div>
                </div>
            `;
        });

        group.innerHTML = html;
        container.appendChild(group);
        initCompetenceEasterEggs(group);
    }

    // Bases de données
    if (competences.bases_de_donnees && competences.bases_de_donnees.length > 0) {
        const group = document.createElement('div');
        group.className = 'competences-group';

        let html = '<div class="competences-group-title">Bases de Données</div>';

        // Trier par priorité
        const sorted = [...competences.bases_de_donnees].sort((a, b) => (a.priorite || 999) - (b.priorite || 999));

        sorted.forEach(comp => {
            const competenceId = `competence-${competenceIdCounter++}`;
            const emoji = comp.emoji || '';
            const color = comp.color || '';
            html += `
                <div class="competence-item" data-competence-id="${competenceId}" data-competence-name="${comp.nom}"${emoji ? ` data-emoji="${emoji}"` : ''}${color ? ` data-color="${color}"` : ''}>
                    <div class="competence-header">
                        <span class="competence-name">${comp.nom}</span>
                        <span class="competence-level">${comp.niveau}%</span>
                    </div>
                    <div class="competence-bar">
                        <div class="competence-bar-fill" style="width: ${comp.niveau}%"></div>
                    </div>
                </div>
            `;
        });

        group.innerHTML = html;
        container.appendChild(group);
        initCompetenceEasterEggs(group);
    }

    // Outils DevOps
    if (competences.outils_devops && competences.outils_devops.length > 0) {
        const group = document.createElement('div');
        group.className = 'competences-group';

        let html = '<div class="competences-group-title">Outils DevOps</div>';

        // Trier par priorité
        const sorted = [...competences.outils_devops].sort((a, b) => (a.priorite || 999) - (b.priorite || 999));

        sorted.forEach(comp => {
            const competenceId = `competence-${competenceIdCounter++}`;
            const emoji = comp.emoji || '';
            const color = comp.color || '';
            html += `
                <div class="competence-item" data-competence-id="${competenceId}" data-competence-name="${comp.nom}"${emoji ? ` data-emoji="${emoji}"` : ''}${color ? ` data-color="${color}"` : ''}>
                    <div class="competence-header">
                        <span class="competence-name">${comp.nom}</span>
                        <span class="competence-level">${comp.niveau}%</span>
                    </div>
                    <div class="competence-bar">
                        <div class="competence-bar-fill" style="width: ${comp.niveau}%"></div>
                    </div>
                </div>
            `;
        });

        group.innerHTML = html;
        container.appendChild(group);
        initCompetenceEasterEggs(group);
    }

    // Langages connus (sans barre de progression pour éviter la surcharge)
    if (competences.langages_connus && competences.langages_connus.length > 0) {
        const group = document.createElement('div');
        group.className = 'competences-group';

        let html = '<div class="competences-group-title">Langages Connus</div><div class="competences-tags">';

        // Trier par priorité
        const sorted = [...competences.langages_connus].sort((a, b) => (a.priorite || 999) - (b.priorite || 999));

        sorted.forEach(comp => {
            html += `<span class="competence-tag">${comp.nom}</span>`;
        });

        html += '</div>';
        group.innerHTML = html;
        container.appendChild(group);
    }

    // Langues
    if (competences.langues && competences.langues.length > 0) {
        const group = document.createElement('div');
        group.className = 'competences-group';

        let html = '<div class="competences-group-title">Langues</div>';

        competences.langues.forEach(langue => {
            const competenceId = `competence-${competenceIdCounter++}`;
            const emoji = langue.emoji || '';
            const color = langue.color || '';
            html += `
                <div class="competence-item" data-competence-id="${competenceId}" data-competence-name="${langue.nom}"${emoji ? ` data-emoji="${emoji}"` : ''}${color ? ` data-color="${color}"` : ''}>
                    <div class="competence-header">
                        <span class="competence-name">${langue.nom}</span>
                        <span class="competence-level">${langue.niveau}</span>
                    </div>
                    <div class="competence-text">${langue.niveau}</div>
                </div>
            `;
        });

        group.innerHTML = html;
        container.appendChild(group);
        initCompetenceEasterEggs(group);
    }
}

// Initialiser les easter eggs et la navigation pour les compétences
function initCompetenceEasterEggs(groupElement) {
    if (!groupElement) return;

    const competenceItems = groupElement.querySelectorAll('.competence-item[data-emoji]');
    
    competenceItems.forEach(item => {
        const emoji = item.getAttribute('data-emoji');
        const color = item.getAttribute('data-color');
        
        if (!emoji) return;

        let isTriggered = false;

        // Gestionnaire mouseenter pour l'easter egg
        item.addEventListener('mouseenter', function() {
            if (isTriggered) return;

            const competenceName = item.querySelector('.competence-name');
            if (!competenceName) return;

            isTriggered = true;

            // Appliquer la couleur personnalisée au hover
            if (color) {
                competenceName.style.color = color;
                competenceName.style.transition = 'color 0.3s ease';
            }

            // Créer l'élément emoji
            const emojiElement = document.createElement('span');
            emojiElement.className = 'competence-emoji-easter';
            emojiElement.textContent = emoji;
            emojiElement.style.opacity = '0';
            
            // Positionner l'emoji au-dessus du nom
            const rect = competenceName.getBoundingClientRect();
            const itemRect = item.getBoundingClientRect();
            emojiElement.style.position = 'absolute';
            emojiElement.style.left = (rect.left - itemRect.left + rect.width / 2) + 'px';
            emojiElement.style.top = (rect.top - itemRect.top) + 'px';
            emojiElement.style.transform = 'translate(-50%, -100%)';
            emojiElement.style.fontSize = '1.5rem';
            emojiElement.style.pointerEvents = 'none';
            emojiElement.style.zIndex = '1000';
            
            item.appendChild(emojiElement);

            // Fade-in puis animation
            requestAnimationFrame(() => {
                emojiElement.style.transition = 'opacity 0.2s ease-in';
                emojiElement.style.opacity = '1';
                
                setTimeout(() => {
                    emojiElement.style.transition = 'transform 0.8s ease-out, opacity 0.8s ease-out';
                    emojiElement.style.transform = 'translate(-50%, calc(-100% - 30px)) rotate(15deg)';
                    emojiElement.style.opacity = '0';
                    
                    setTimeout(() => {
                        if (emojiElement.parentNode) {
                            emojiElement.parentNode.removeChild(emojiElement);
                        }
                    }, 800);
                }, 200);
            });
        });

        // Gestionnaire mouseleave pour réinitialiser
        item.addEventListener('mouseleave', function() {
            isTriggered = false;
            
            // Réinitialiser la couleur
            const competenceName = item.querySelector('.competence-name');
            if (competenceName && color) {
                competenceName.style.color = '';
            }
        });

    });
}

// Initialiser la navigation depuis les tags de technologies vers les compétences
function initTechTagNavigation() {
    const techTags = document.querySelectorAll('.timeline-tech-tag');
    
    // Table de correspondance pour les noms alternatifs
    const techNameMapping = {
        'js': 'JavaScript',
        'javascript': 'JavaScript',
        'ts': 'TypeScript',
        'typescript': 'TypeScript',
        'csharp': 'C#',
        'c#': 'C#',
        'cpp': 'C/C++',
        'c++': 'C/C++',
        'cc++': 'C/C++',
        'mysql/mariadb': 'MySQL/MariaDB',
        'mariadb': 'MySQL/MariaDB',
        'postgresql': 'PostgreSQL',
        'postgres': 'PostgreSQL',
        'oracle sql': 'Oracle SQL',
        'html/css': 'HTML/CSS',
        'html': 'HTML/CSS',
        'css': 'HTML/CSS',
        'powershell': 'PowerShell',
        'bash': 'Bash',
        'php': 'PHP',
        'symfony': 'Symfony',
        'laravel': 'Laravel',
        'java': 'Java',
        'kotlin': 'Kotlin',
        'python': 'Python',
        'sql': 'SQL',
        'go': 'Go',
        'rust': 'Rust',
        'ruby': 'Ruby',
        'ocaml': 'Ocaml',
        'docker': 'Docker',
        'git': 'Git',
        'qt': 'Qt'
    };
    
    techTags.forEach(tag => {
        tag.style.cursor = 'pointer';
        tag.setAttribute('title', 'Cliquer pour voir cette compétence');
        
        tag.addEventListener('click', function() {
            const techName = tag.getAttribute('data-tech-name') || tag.textContent.trim();
            
            // Normaliser le nom (minuscules, sans espaces superflus)
            const normalizedTechName = techName.toLowerCase().trim();
            
            // Chercher dans la table de correspondance
            const mappedName = techNameMapping[normalizedTechName] || techName;
            
            // Gérer les cas avec slash (ex: "PHP/Symfony")
            const techNamesToSearch = mappedName.includes('/') 
                ? mappedName.split('/').map(t => t.trim())
                : [mappedName];
            
            // Chercher la compétence correspondante
            let foundCompetence = null;
            for (const nameToSearch of techNamesToSearch) {
                // Recherche exacte d'abord
                foundCompetence = document.querySelector(
                    `.competence-item[data-competence-name="${nameToSearch}"]`
                );
                
                if (foundCompetence) break;
                
                // Recherche case-insensitive
                const allCompetences = document.querySelectorAll('.competence-item[data-competence-name]');
                for (const comp of allCompetences) {
                    const compName = comp.getAttribute('data-competence-name');
                    if (compName && compName.toLowerCase() === nameToSearch.toLowerCase()) {
                        foundCompetence = comp;
                        break;
                    }
                }
                
                if (foundCompetence) break;
            }
            
            if (foundCompetence) {
                // Scroller vers la section compétences
                const competencesSection = document.getElementById('competences-section');
                if (competencesSection) {
                    competencesSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                
                // Scroller vers la compétence spécifique avec un petit délai
                setTimeout(() => {
                    foundCompetence.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Ajouter la classe de surbrillance
                    foundCompetence.classList.add('competence-item-highlight');
                    
                    // Retirer la classe après l'animation
                    setTimeout(() => {
                        foundCompetence.classList.remove('competence-item-highlight');
                    }, 1000);
                }, 300);
            }
        });
    });
}

// Rendre la section résumé
function renderResume(contact) {
    const container = document.getElementById('resume-text');
    if (!container || !contact || !contact.resume_profil) return;

    container.textContent = contact.resume_profil;
}

// Rendre la section contact
function renderContact(contact) {
    const container = document.getElementById('contact-container');
    if (!container || !contact) return;

    container.innerHTML = '';

    const etatCivil = contact.etat_civil || {};
    const reseaux = contact.reseaux_sociaux || {};

    // Nom et titre
    if (etatCivil.prenom || etatCivil.nom || etatCivil.titre) {
        const item = document.createElement('div');
        item.className = 'contact-item';
        let name = '';
        if (etatCivil.prenom && etatCivil.nom) {
            name = `${etatCivil.prenom} ${etatCivil.nom}`;
        } else if (etatCivil.nom) {
            name = etatCivil.nom;
        }
        item.innerHTML = `
            <div class="contact-label">${etatCivil.titre || 'Identité'}</div>
            <div class="contact-value">${name}${etatCivil.titre ? `<br><span style="opacity: 0.8; font-size: 0.9em;">${etatCivil.titre}</span>` : ''}</div>
        `;
        container.appendChild(item);
    }

    // Email
    if (etatCivil.email) {
        const item = document.createElement('div');
        item.className = 'contact-item';
        item.innerHTML = `
            <div class="contact-label">Email</div>
            <div class="contact-value">
                <a href="mailto:${etatCivil.email}" class="contact-link">${etatCivil.email}</a>
            </div>
        `;
        container.appendChild(item);
    }

    // Téléphone
    if (etatCivil.telephone) {
        const item = document.createElement('div');
        item.className = 'contact-item';
        item.innerHTML = `
            <div class="contact-label">Téléphone</div>
            <div class="contact-value">
                <a href="tel:${etatCivil.telephone.replace(/\s/g, '')}" class="contact-link">${etatCivil.telephone}</a>
            </div>
        `;
        container.appendChild(item);
    }

    // Localisation
    if (etatCivil.localisation) {
        const item = document.createElement('div');
        item.className = 'contact-item';
        item.innerHTML = `
            <div class="contact-label">Localisation</div>
            <div class="contact-value">${etatCivil.localisation}${etatCivil.permis ? ` • ${etatCivil.permis}` : ''}</div>
        `;
        container.appendChild(item);
    }

    // Site web
    if (etatCivil.site_web) {
        const item = document.createElement('div');
        item.className = 'contact-item';
        item.innerHTML = `
            <div class="contact-label">Site Web</div>
            <div class="contact-value">
                <a href="${etatCivil.site_web}" target="_blank" class="contact-link">${etatCivil.site_web.replace(/^https?:\/\//, '')}</a>
            </div>
        `;
        container.appendChild(item);
    }

    // Réseaux sociaux
    if (Object.keys(reseaux).length > 0) {
        const item = document.createElement('div');
        item.className = 'contact-item';
        
        let html = '<div class="contact-label">Réseaux</div><div class="contact-reseaux">';
        
        if (reseaux.github) {
            const githubUrl = reseaux.github.startsWith('@') ? `https://github.com/${reseaux.github.substring(1)}` : reseaux.github;
            html += `
                <div class="contact-reseau-item">
                    <a href="${githubUrl}" target="_blank" class="contact-link">GitHub ${reseaux.github}</a>
                </div>
            `;
        }
        
        if (reseaux.linkedin) {
            const linkedinUrl = reseaux.linkedin.startsWith('@') ? `https://linkedin.com/in/${reseaux.linkedin.substring(1)}` : reseaux.linkedin;
            html += `
                <div class="contact-reseau-item">
                    <a href="${linkedinUrl}" target="_blank" class="contact-link">LinkedIn ${reseaux.linkedin}</a>
                </div>
            `;
        }
        
        if (reseaux.twitter_x) {
            const twitterUrl = reseaux.twitter_x.startsWith('@') ? `https://twitter.com/${reseaux.twitter_x.substring(1)}` : reseaux.twitter_x;
            html += `
                <div class="contact-reseau-item">
                    <a href="${twitterUrl}" target="_blank" class="contact-link">Twitter/X ${reseaux.twitter_x}</a>
                </div>
            `;
        }
        
        html += '</div>';
        item.innerHTML = html;
        container.appendChild(item);
    }
}

// Rendre la section certifications
function renderCertifications(certifications) {
    const container = document.getElementById('certifications-container');
    if (!container || !certifications || certifications.length === 0) return;

    container.innerHTML = '';

    certifications.forEach(cert => {
        const item = document.createElement('div');
        item.className = 'certification-item';

        let html = `<div class="certification-title">${cert.titre}</div>`;

        if (cert.annees && cert.annees.length > 0) {
            html += `<div class="certification-date">${cert.annees.join(' - ')}</div>`;
        }

        if (cert.details) {
            if (Array.isArray(cert.details)) {
                html += '<ul class="certification-details">';
                cert.details.forEach(detail => {
                    html += `<li class="certification-detail-item">${detail}</li>`;
                });
                html += '</ul>';
            } else {
                html += `<div class="certification-detail-text">${cert.details}</div>`;
            }
        }

        if (cert.role) {
            html += `<div class="certification-role">${cert.role}</div>`;
        }

        item.innerHTML = html;
        container.appendChild(item);
    });
}

// Rendre la section références
function renderReferences(references) {
    const container = document.getElementById('references-container');
    if (!container || !references || references.length === 0) return;

    container.innerHTML = '';

    references.forEach(ref => {
        const item = document.createElement('div');
        item.className = 'reference-item';

        let html = `
            <div class="reference-name">${ref.nom}</div>
            <div class="reference-role">${ref.role}</div>
            <div class="reference-org">${ref.organisation}</div>
        `;

        if (ref.telephone) {
            html += `
                <div class="reference-contact">
                    <a href="tel:${ref.telephone.replace(/\s/g, '')}" class="contact-link">${ref.telephone}</a>
                </div>
            `;
        }

        item.innerHTML = html;
        container.appendChild(item);
    });
}

// Initialiser le titre avec clipped-text
/**
 * Transforme le titre "CV & Contact" en texte clippé SVG responsive
 * Similaire à Réalisations, réagit aux changements de taille de fenêtre
 */
function initCVTitle() {
    let currentSVG = null; // Stocker le SVG actuel pour pouvoir le remplacer au resize
    
    function createCVTitleClippedText() {
        if (typeof createClippedText === 'undefined' || typeof replaceElementWithClippedText === 'undefined') {
            setTimeout(createCVTitleClippedText, 100);
            return;
        }

        // Trouver l'élément H1 "CV & Contact"
        const titleElement = document.querySelector('.cv-title.clipped');
        if (!titleElement) {
            console.warn('Élément .cv-title.clipped non trouvé');
            return;
        }

        // Si c'est déjà un SVG (lors d'un resize), on doit le remplacer
        const isSVG = titleElement.tagName === 'svg';
        const text = isSVG ? (titleElement.dataset.originalText || 'CV & Contact') : 'CV & Contact';

        // Calculer la taille de police de manière responsive comme Réalisations
        const viewportWidth = window.innerWidth;
        // Ajuster la taille : similaire à Réalisations car c'est aussi un titre de page
        const fontSize = Math.max(Math.min(viewportWidth * 0.15, 300), 120); // Min 120px, max 300px, responsive

        console.log('CV & Contact - FontSize calculé:', fontSize, 'Viewport:', viewportWidth);

        // Paramètres similaires à Réalisations
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
                console.log('CV & Contact transformé avec succès');
            } else {
                console.warn('Échec de la transformation de CV & Contact');
            }
        }
    }

    // Fonction pour gérer le resize avec debounce
    let resizeTimeout;
    function handleResize() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            console.log('Resize détecté, régénération de CV & Contact...');
            createCVTitleClippedText();
        }, 150); // Debounce de 150ms
    }

    // Attendre que la page et la police soient chargées
    function init() {
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(() => {
                setTimeout(createCVTitleClippedText, 300);
                // Ajouter l'écouteur de resize
                window.addEventListener('resize', handleResize);
            });
        } else {
            window.addEventListener('load', function() {
                setTimeout(createCVTitleClippedText, 600);
                // Ajouter l'écouteur de resize
                window.addEventListener('resize', handleResize);
            });
        }
    }

    init();
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', async () => {
    // Charger toutes les données JSON
    const data = await loadCVData();
    
    if (data) {
        // Rendre chaque section
        renderResume(data.contact);
        renderExperience(data.experience);
        renderDiplomes(data.diplomes);
        renderHobby(data.hobby);
        renderCompetences(data.competences);
        renderContact(data.contact);
        renderCertifications(data.certifications);
        renderReferences(data.references);
    }
    
    // Initialiser le titre avec clipped-text
    initCVTitle();
});
