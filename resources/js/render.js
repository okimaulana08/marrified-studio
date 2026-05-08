// Public render-page interactions: cover open, smooth scroll.

document.addEventListener('DOMContentLoaded', () => {
    const cover = document.getElementById('section-cover');
    const main = document.getElementById('invitation-main');

    document.querySelectorAll('[data-cover-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            cover?.classList.add('cover--hidden');
            window.scrollTo({ top: main?.offsetTop ?? 0, behavior: 'smooth' });
        });
    });
});
