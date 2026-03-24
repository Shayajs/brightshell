/**
 * Menu latéral responsive des portails connectés.
 */
document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('.portal-shell');
    const toggle = document.querySelector('[data-portal-menu-toggle]');
    const backdrop = document.querySelector('[data-portal-sidebar-backdrop]');

    if (!shell || !toggle) {
        return;
    }

    const close = () => {
        shell.classList.remove('portal-sidebar-open');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
        const isOpen = shell.classList.toggle('portal-sidebar-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
    });

    backdrop?.addEventListener('click', close);

    const isMobileSidebar = () => window.matchMedia('(max-width: 1023px)').matches;

    shell.querySelectorAll('.portal-nav a').forEach((link) => {
        link.addEventListener('click', () => {
            if (isMobileSidebar()) {
                close();
            }
        });
    });

    // Scroll automatique vers l'item actif dans la nav sidebar
    const nav = shell.querySelector('.portal-nav');
    const activeLink = nav?.querySelector('[aria-current="page"]');

    if (nav && activeLink) {
        const navRect = nav.getBoundingClientRect();
        const linkRect = activeLink.getBoundingClientRect();

        const isAbove = linkRect.top < navRect.top;
        const isBelow = linkRect.bottom > navRect.bottom;

        if (isAbove || isBelow) {
            // Centre l'item actif dans la zone scrollable de la nav
            const scrollTarget = activeLink.offsetTop - (nav.clientHeight / 2) + (activeLink.clientHeight / 2);
            nav.scrollTo({ top: Math.max(0, scrollTarget), behavior: 'instant' });
        }
    }
});
