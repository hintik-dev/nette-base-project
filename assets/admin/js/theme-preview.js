const group = document.querySelector('[data-theme-preview]');
if (group) {
    group.addEventListener('change', (e) => {
        const input = e.target;
        if (input.tagName !== 'INPUT' || input.type !== 'radio') return;

        const theme = input.value;
        const resolved = theme === 'system'
            ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
            : theme;

        document.documentElement.setAttribute('data-bs-theme', resolved);

        // Drží AdminLTE vlastní perzistenci motivu (localStorage "lte-theme") v souladu
        // s náhledem, aby ji AdminLTE nepřebilo zpět na systémové preference.
        try { localStorage.setItem('lte-theme', resolved); } catch (e) {}

        const sidebar = document.querySelector('.app-sidebar');
        if (sidebar) {
            sidebar.setAttribute('data-bs-theme', resolved);
            sidebar.classList.toggle('sidebar-dark-primary', resolved === 'dark');
            sidebar.classList.toggle('sidebar-light-primary', resolved !== 'dark');
        }
    });
}
