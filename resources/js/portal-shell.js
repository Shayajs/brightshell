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
});
