document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.getElementById('menu-button');
    const sideMenu = document.getElementById('side-menu');
    const menuOverlay = document.getElementById('menu-overlay');

    function openMenu() {
        sideMenu.classList.remove('-translate-x-full');
        menuOverlay.classList.remove('hidden');
    }

    function closeMenu() {
        sideMenu.classList.add('-translate-x-full');
        menuOverlay.classList.add('hidden');
    }

    menuButton.addEventListener('click', openMenu);
    menuOverlay.addEventListener('click', closeMenu);

    // App Share logic
    const appShareButton = document.getElementById('app-share');
    if (appShareButton) {
        appShareButton.addEventListener('click', () => {
            if (navigator.share) {
                navigator.share({
                    title: 'ClassmateApp',
                    text: 'Check out ClassmateApp for study materials!',
                    url: window.location.origin,
                })
                .then(() => console.log('Successful share'))
                .catch((error) => console.log('Error sharing', error));
            } else {
                alert('Web Share API is not supported in your browser. You can manually copy the link: ' + window.location.origin);
            }
        });
    }
});