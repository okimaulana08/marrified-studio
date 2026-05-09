import Swiper from 'swiper';
import { Navigation, Pagination, Keyboard, Mousewheel, A11y } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

document.addEventListener('DOMContentLoaded', () => {
    const deckEl = document.querySelector('.invitation-deck');
    if (!deckEl) return;

    const swiper = new Swiper(deckEl, {
        modules: [Navigation, Pagination, Keyboard, Mousewheel, A11y],
        direction: 'vertical',
        speed: 650,
        slidesPerView: 1,
        spaceBetween: 0,
        grabCursor: true,
        keyboard: { enabled: true, onlyInViewport: true },
        mousewheel: {
            forceToAxis: true,
            sensitivity: 1,
            thresholdDelta: 30,
            releaseOnEdges: true,
        },
        pagination: {
            el: '.deck-pagination',
            clickable: true,
            dynamicBullets: true,
            dynamicMainBullets: 3,
        },
        navigation: {
            nextEl: '.deck-nav-next',
            prevEl: '.deck-nav-prev',
        },
        a11y: {
            prevSlideMessage: 'Halaman sebelumnya',
            nextSlideMessage: 'Halaman berikutnya',
            paginationBulletMessage: 'Ke halaman {{index}}',
        },
        noSwipingClass: 'no-swipe',
        nested: true,
    });

    // Cover open button → advance deck.
    document.querySelectorAll('[data-cover-open]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            swiper.slideNext(800);
        });
    });

    // ============================================================
    // Lottie lazy-loader. The lottie-web bundle is ~250KB; we only
    // want to pay that cost when a theme actually uses lottie. The
    // dynamic import makes Vite emit it as a separate chunk that
    // loads on the first slide that contains a [data-lottie] node.
    // ============================================================
    let lottieModulePromise = null;
    const loadLottieModule = () => {
        if (!lottieModulePromise) {
            // `lottie_light` excludes the AE expression engine (uses no `eval`),
            // which is unnecessary for ornamental wedding animations and
            // strips a large chunk of weight off the player bundle.
            lottieModulePromise = import('lottie-web/build/player/lottie_light.js').then((m) => m.default);
        }
        return lottieModulePromise;
    };

    /** WeakMap<HTMLElement, AnimationItem> — one player per slot element. */
    const lottiePlayers = new WeakMap();

    const initLottiesIn = async (slideEl) => {
        if (!slideEl) return;
        const targets = slideEl.querySelectorAll('.layout-lottie[data-lottie]');
        if (!targets.length) return;

        const lottie = await loadLottieModule();

        targets.forEach((el) => {
            if (lottiePlayers.has(el)) return;
            try {
                const player = lottie.loadAnimation({
                    container: el,
                    renderer: 'svg',
                    loop: el.dataset.lottieLoop !== '0',
                    autoplay: true,
                    path: el.dataset.lottie,
                });
                lottiePlayers.set(el, player);
            } catch (err) {
                console.warn('Lottie load failed for', el.dataset.lottie, err);
            }
        });
    };

    /** Pause lotties on inactive slides; resume on active. Saves CPU/battery. */
    const reconcileLottiePlayback = () => {
        const activeSlide = deckEl.querySelector('.swiper-slide-active');
        document.querySelectorAll('.layout-lottie[data-lottie]').forEach((el) => {
            const player = lottiePlayers.get(el);
            if (!player) return;
            const inActive = activeSlide?.contains(el);
            if (inActive) player.play();
            else player.pause();
        });
    };

    // ============================================================
    // Slide-entered hook: marks `.is-entered` for Phase 3 anim_in
    // and lazy-inits any lotties on that slide for Phase 4.
    // ============================================================
    const onSlideEntered = (slideEl) => {
        if (!slideEl) return;
        if (!slideEl.classList.contains('is-entered')) {
            slideEl.classList.add('is-entered');
        }
        initLottiesIn(slideEl);
    };

    swiper.on('slideChangeTransitionEnd', () => {
        onSlideEntered(deckEl.querySelector('.swiper-slide-active'));
        reconcileLottiePlayback();
    });
    onSlideEntered(deckEl.querySelector('.swiper-slide-active')); // initial cover

    // Body classes for cover/end states (used by deck nav UI hints).
    swiper.on('slideChange', () => {
        document.body.classList.toggle('deck-at-cover', swiper.activeIndex === 0);
        document.body.classList.toggle('deck-at-end', swiper.isEnd);
    });
    document.body.classList.add('deck-at-cover');

    // Pause lotties when tab is backgrounded; resume when foreground.
    document.addEventListener('visibilitychange', () => {
        document.querySelectorAll('.layout-lottie[data-lottie]').forEach((el) => {
            const player = lottiePlayers.get(el);
            if (!player) return;
            if (document.hidden) player.pause();
            else if (deckEl.querySelector('.swiper-slide-active')?.contains(el)) player.play();
        });
    });

    // Expose for debugging.
    window._deck = swiper;
    window._lottiePlayers = lottiePlayers;
});
