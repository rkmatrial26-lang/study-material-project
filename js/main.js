document.addEventListener('DOMContentLoaded', () => {
    // Page Loader for navigation
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function(event) {
            const href = this.getAttribute('href');
            // Ignore javascript links, links opening in a new tab, or anchor links
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || this.target === '_blank') {
                return;
            }
            event.preventDefault();
            const loader = document.getElementById('loader-overlay');
            if (loader) loader.style.display = 'flex';
            window.location.href = href;
        });
    });

    // We no longer need JavaScript to control the accordion.
    // The browser's native <details> element handles it automatically.
});