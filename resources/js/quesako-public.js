(() => {
    const body = document.body;
    if (!body) return;

    const isHome = body.classList.contains('home-vitrine');
    const isQuesako = body.classList.contains('quesako-vitrine');
    if (!isHome && !isQuesako) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (isQuesako) {
        const hero = document.querySelector('.quesako-hero-title');
        if (hero && !reduceMotion) {
            requestAnimationFrame(() => hero.classList.add('is-visible'));
        } else if (hero) {
            hero.classList.add('is-visible');
        }
    }

    document.querySelectorAll('a[data-transition-link]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (reduceMotion) return;
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            if (link.target === '_blank') return;
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#')) return;

            event.preventDefault();
            body.classList.add('page-transitioning');
            window.setTimeout(() => {
                window.location.assign(href);
            }, 220);
        });
    });
})();
