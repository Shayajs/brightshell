/**
 * BRIGHTSHELL - CV Page Interactions (SSR content, interactions only)
 */

document.addEventListener('DOMContentLoaded', function () {
    initTimelineToggles();
    initTechTagNavigation();
    document.querySelectorAll('.competences-group').forEach(initCompetenceEasterEggs);
});

function initTimelineToggles() {
    document.querySelectorAll('.timeline-toggle-btn').forEach((btn) => {
        const targetId = btn.dataset.target;
        if (!targetId) return;
        const container = document.getElementById(targetId);
        if (!container) return;

        const items = container.querySelectorAll('.timeline-item');
        const hiddenCount = items.length - 3;

        btn.addEventListener('click', () => {
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            items.forEach((item, i) => {
                item.classList.toggle('timeline-item-hidden', !expanded && i >= 3);
            });
            btn.setAttribute('aria-expanded', !expanded);
            btn.textContent = expanded ? `Voir plus (${hiddenCount})` : 'Voir moins';
        });
    });
}

function initCompetenceEasterEggs(groupElement) {
    if (!groupElement) return;
    const competenceItems = groupElement.querySelectorAll('.competence-item[data-emoji]');

    competenceItems.forEach((item) => {
        const emoji = item.getAttribute('data-emoji');
        const color = item.getAttribute('data-color');
        if (!emoji) return;

        let isTriggered = false;

        item.addEventListener('mouseenter', function () {
            if (isTriggered) return;
            const competenceName = item.querySelector('.competence-name');
            if (!competenceName) return;

            isTriggered = true;
            if (color) {
                competenceName.style.color = color;
                competenceName.style.transition = 'color 0.3s ease';
            }

            const emojiElement = document.createElement('span');
            emojiElement.className = 'competence-emoji-easter';
            emojiElement.textContent = emoji;
            emojiElement.style.opacity = '0';

            const rect = competenceName.getBoundingClientRect();
            const itemRect = item.getBoundingClientRect();
            Object.assign(emojiElement.style, {
                position: 'absolute',
                left: rect.left - itemRect.left + rect.width / 2 + 'px',
                top: rect.top - itemRect.top + 'px',
                transform: 'translate(-50%, -100%)',
                fontSize: '1.5rem',
                pointerEvents: 'none',
                zIndex: '1000',
            });
            item.appendChild(emojiElement);

            requestAnimationFrame(() => {
                emojiElement.style.transition = 'opacity 0.2s ease-in';
                emojiElement.style.opacity = '1';
                setTimeout(() => {
                    emojiElement.style.transition = 'transform 0.8s ease-out, opacity 0.8s ease-out';
                    emojiElement.style.transform = 'translate(-50%, calc(-100% - 30px)) rotate(15deg)';
                    emojiElement.style.opacity = '0';
                    setTimeout(() => emojiElement.remove(), 800);
                }, 200);
            });
        });

        item.addEventListener('mouseleave', function () {
            isTriggered = false;
            const competenceName = item.querySelector('.competence-name');
            if (competenceName && color) competenceName.style.color = '';
        });
    });
}

function initTechTagNavigation() {
    const techNameMapping = {
        js: 'JavaScript', javascript: 'JavaScript', ts: 'TypeScript', typescript: 'TypeScript',
        csharp: 'C#', 'c#': 'C#', cpp: 'C/C++', 'c++': 'C/C++', cc: 'C/C++',
        'mysql/mariadb': 'MySQL/MariaDB', mariadb: 'MySQL/MariaDB', postgresql: 'PostgreSQL', postgres: 'PostgreSQL',
        'oracle sql': 'Oracle SQL', 'html/css': 'HTML/CSS', html: 'HTML/CSS', css: 'HTML/CSS',
        powershell: 'PowerShell', bash: 'Bash', php: 'PHP', symfony: 'Symfony', laravel: 'Laravel',
        java: 'Java', kotlin: 'Kotlin', python: 'Python', sql: 'SQL', go: 'Go', rust: 'Rust',
        ruby: 'Ruby', ocaml: 'Ocaml', docker: 'Docker', git: 'Git', qt: 'Qt',
    };

    document.querySelectorAll('.timeline-tech-tag').forEach((tag) => {
        tag.style.cursor = 'pointer';
        tag.setAttribute('title', 'Cliquer pour voir cette compétence');

        tag.addEventListener('click', function () {
            const techName = tag.getAttribute('data-tech-name') || tag.textContent.trim();
            const normalizedTechName = techName.toLowerCase().trim();
            const mappedName = techNameMapping[normalizedTechName] || techName;
            const techNamesToSearch = mappedName.includes('/')
                ? mappedName.split('/').map((t) => t.trim())
                : [mappedName];

            let foundCompetence = null;
            for (const nameToSearch of techNamesToSearch) {
                foundCompetence = document.querySelector(
                    `.competence-item[data-competence-name="${nameToSearch}"]`
                );
                if (foundCompetence) break;
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
                const competencesSection = document.getElementById('competences-section');
                if (competencesSection) competencesSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setTimeout(() => {
                    foundCompetence.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    foundCompetence.classList.add('competence-item-highlight');
                    setTimeout(() => foundCompetence.classList.remove('competence-item-highlight'), 1000);
                }, 300);
            }
        });
    });
}
