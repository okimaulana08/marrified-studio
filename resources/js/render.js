import Swiper from 'swiper';
import { Navigation, Pagination, Keyboard, Mousewheel, A11y } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

/**
 * Alpine factory for the gallery lightbox. Each gallery <section> wraps with
 * `x-data="galleryLightbox([...urls])"` and exposes open/close/prev/next.
 *
 * Lightbox state lives in component scope so multiple galleries on the same
 * page (unlikely here but clean) won't conflict. Body scroll is locked while
 * the overlay is open so the swiper deck doesn't accidentally page-change
 * underneath.
 */
window.galleryLightbox = (urls) => ({
    urls: Array.isArray(urls) ? urls : [],
    index: 0,
    active: false,
    // Swipe state
    _touchStartX: null,
    _touchStartY: null,
    _touchStartT: 0,
    // Toast (used as fallback when navigator.share unavailable)
    toast: false,
    toastText: '',
    _toastId: null,

    get total() { return this.urls.length; },
    get canPrev() { return this.index > 0; },
    get canNext() { return this.index < this.urls.length - 1; },
    get canShare() { return typeof navigator !== 'undefined' && typeof navigator.share === 'function'; },

    open(i) {
        if (i < 0 || i >= this.urls.length) return;
        this.index = i;
        this.active = true;
        document.body.style.overflow = 'hidden';
    },
    close() {
        this.active = false;
        document.body.style.overflow = '';
    },
    prev() { if (this.canPrev) this.index--; },
    next() { if (this.canNext) this.index++; },

    /** Detect horizontal swipe → navigate to prev/next slide. */
    onTouchStart(e) {
        const t = e.touches?.[0];
        if (!t || e.touches.length > 1) {
            // Multi-touch = pinch zoom, let the browser handle it.
            this._touchStartX = null;
            return;
        }
        this._touchStartX = t.clientX;
        this._touchStartY = t.clientY;
        this._touchStartT = Date.now();
    },
    onTouchEnd(e) {
        if (this._touchStartX === null) return;
        const t = e.changedTouches?.[0];
        if (!t) return;
        const dx = t.clientX - this._touchStartX;
        const dy = t.clientY - this._touchStartY;
        const dt = Date.now() - this._touchStartT;
        this._touchStartX = null;
        // Horizontal-dominant, fast, and far enough.
        if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) * 1.5 && dt < 600) {
            if (dx < 0) this.next(); else this.prev();
        }
    },

    /** Trigger browser download of the current photo. Uses anchor[download]. */
    downloadCurrent() {
        const url = this.urls[this.index];
        if (!url) return;
        const a = document.createElement('a');
        a.href = url;
        a.download = `foto-${this.index + 1}.jpg`;
        // Open in new tab as a fallback for cross-origin URLs that block direct download.
        a.target = '_blank';
        a.rel = 'noopener';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    },

    /** Native Web Share if available, otherwise copy URL to clipboard + toast. */
    async shareCurrent() {
        const url = this.urls[this.index];
        if (!url) return;
        const absoluteUrl = new URL(url, window.location.href).toString();
        if (this.canShare) {
            try {
                await navigator.share({ url: absoluteUrl, title: 'Foto Undangan' });
                return;
            } catch (e) {
                if (e?.name === 'AbortError') return;
                // fall through to clipboard fallback
            }
        }
        try {
            await navigator.clipboard.writeText(absoluteUrl);
            this._showToast('Link foto disalin');
        } catch (e) {
            this._showToast('Tidak bisa share/copy');
        }
    },

    _showToast(text) {
        this.toastText = text;
        this.toast = true;
        clearTimeout(this._toastId);
        this._toastId = setTimeout(() => { this.toast = false; }, 1800);
    },
});

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

    // Lock cover slide: user must click "Open Invitation" to advance.
    // Without this, swiping past the cover skips music autoplay (which
    // requires the explicit cover-button user-gesture). Unlocked on click.
    swiper.allowSlideNext = false;
    swiper.mousewheel?.disable?.();

    // ============================================================
    // Background music: lazy-load src + start playing on cover-open click.
    // Browsers block audio.play() before any user gesture, so we leverage
    // the existing "Open Invitation" click as the unlock event. Mute toggle
    // floats bottom-right, swap icons via [hidden] attribute.
    // ============================================================
    const bgm = document.getElementById('invitation-bgm');
    const bgmToggle = document.querySelector('[data-bgm-toggle]');
    let bgmStarted = false;

    const startBgm = () => {
        if (! bgm || bgmStarted) return;
        const src = bgm.dataset.src;
        if (! src) return;
        bgm.src = src;
        bgmStarted = true;
        bgm.play().catch(() => {
            // Autoplay rejected (rare after user-gesture). Show toggle anyway
            // so user can manually start.
        });
        if (bgmToggle) bgmToggle.hidden = false;
    };

    const updateBgmToggleIcons = () => {
        if (! bgmToggle || ! bgm) return;
        const onIcon = bgmToggle.querySelector('.bgm-toggle-on');
        const offIcon = bgmToggle.querySelector('.bgm-toggle-off');
        const muted = bgm.paused || bgm.muted;
        if (onIcon) onIcon.classList.toggle('is-hidden', muted);
        if (offIcon) offIcon.classList.toggle('is-hidden', ! muted);
        bgmToggle.setAttribute('aria-label', muted ? 'Hidupkan musik' : 'Matikan musik');
    };

    if (bgmToggle && bgm) {
        bgmToggle.addEventListener('click', () => {
            if (bgm.paused) {
                bgm.play().catch(() => {});
            } else {
                bgm.pause();
            }
            updateBgmToggleIcons();
        });
        bgm.addEventListener('play', updateBgmToggleIcons);
        bgm.addEventListener('pause', updateBgmToggleIcons);
    }

    // Pause when tab is hidden, resume when visible (only if was playing).
    if (bgm) {
        let wasPlayingBeforeHide = false;
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                wasPlayingBeforeHide = ! bgm.paused;
                if (wasPlayingBeforeHide) bgm.pause();
            } else if (wasPlayingBeforeHide) {
                bgm.play().catch(() => {});
            }
        });
    }

    // Cover open button → advance deck + unlock bgm playback.
    document.querySelectorAll('[data-cover-open]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            startBgm();
            swiper.allowSlideNext = true;
            swiper.mousewheel?.enable?.();
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

    // ============================================================
    // Scroll-then-swipe gate. When a slide's `.page-content` overflows
    // the viewport, the user must scroll within the page before swipe
    // advances the deck. Behavior:
    //   - at TOP edge  → allowSlidePrev = true,  next stays locked until bottom
    //   - at BOTTOM    → allowSlideNext = true,  prev stays locked until top
    //   - mid-scroll   → both directions blocked, native scroll handles it
    //   - non-overflow → both allowed (no double-step)
    // The cover slide keeps its existing user-gesture lock on next; we
    // only run the gate once the deck is unlocked AND the slide index > 0.
    // ============================================================
    const EDGE_TOLERANCE = 2; // px — covers sub-pixel rounding
    let activeScrollEl = null;
    let activeScrollHandler = null;

    const isCoverActive = () => swiper.activeIndex === 0;

    const computeEdgeState = (el) => {
        const overflow = el.scrollHeight - el.clientHeight > EDGE_TOLERANCE;
        if (!overflow) {
            return { overflow: false, atTop: true, atBottom: true };
        }
        const atTop = el.scrollTop <= EDGE_TOLERANCE;
        const atBottom = el.scrollHeight - el.clientHeight - el.scrollTop <= EDGE_TOLERANCE;
        return { overflow: true, atTop, atBottom };
    };

    const applyGate = () => {
        const slideEl = deckEl.querySelector('.swiper-slide-active');
        if (!slideEl) return;
        const scrollEl = slideEl.querySelector('.page-content');
        if (!scrollEl) return;

        const { overflow, atTop, atBottom } = computeEdgeState(scrollEl);

        slideEl.classList.toggle('is-overflowing', overflow);
        slideEl.classList.toggle('at-top', atTop);
        slideEl.classList.toggle('at-bottom', atBottom);

        // Cover slide: leave touch/next-lock to the existing cover-open gesture.
        if (isCoverActive()) return;

        // For overflowing slides: tag .page-content with the noSwipingClass
        // ("no-swipe", configured on Swiper instance). Swiper walks up the
        // DOM at touchStart; finding this class releases the gesture entirely
        // to native scroll. Also disable the Mousewheel module so wheel
        // events feed the inner overflow-y scroll instead of being consumed
        // by Swiper. Advance/prev is done via nav arrows or chevron tap.
        scrollEl.classList.toggle('no-swipe', overflow);

        if (overflow) {
            swiper.mousewheel?.disable?.();
        } else {
            swiper.mousewheel?.enable?.();
        }

        // For non-overflow slides, restore default behavior. Swiper's
        // allowSlideNext/Prev stay open so nav arrows and pagination
        // always work programmatically.
        swiper.allowTouchMove = true;
        swiper.allowSlideNext = true;
        swiper.allowSlidePrev = true;
    };

    const bindActiveScrollListener = () => {
        // Detach previous listener (slide changed).
        if (activeScrollEl && activeScrollHandler) {
            activeScrollEl.removeEventListener('scroll', activeScrollHandler);
        }
        const slideEl = deckEl.querySelector('.swiper-slide-active');
        const scrollEl = slideEl?.querySelector('.page-content');
        if (!scrollEl) {
            activeScrollEl = null;
            activeScrollHandler = null;
            return;
        }
        activeScrollEl = scrollEl;
        activeScrollHandler = () => applyGate();
        activeScrollEl.addEventListener('scroll', activeScrollHandler, { passive: true });
        applyGate();
    };

    swiper.on('slideChangeTransitionEnd', bindActiveScrollListener);
    window.addEventListener('resize', () => applyGate(), { passive: true });
    bindActiveScrollListener();

    // Chevron hint at the bottom of overflowing slides is tappable: when
    // user is still mid-scroll, tapping it scrolls to the bottom; once at
    // the bottom, tapping advances to the next slide. This gives a 1-tap
    // alternative to the 2-step "scroll then swipe" gesture on mobile.
    deckEl.addEventListener('click', (e) => {
        const hint = e.target.closest('.page-scroll-hint');
        if (!hint) return;
        const slideEl = hint.closest('.swiper-slide');
        if (!slideEl) return;
        const scrollEl = slideEl.querySelector('.page-content');
        if (!scrollEl) return;
        const { atBottom } = computeEdgeState(scrollEl);
        if (atBottom) {
            // At bottom edge → next slide.
            const next = swiper.activeIndex + 1;
            if (next < swiper.slides.length) {
                swiper.allowSlideNext = true;
                swiper.slideTo(next, 700);
            }
        } else {
            // Mid-scroll → smooth-scroll to bottom so user sees the rest.
            scrollEl.scrollTo({ top: scrollEl.scrollHeight, behavior: 'smooth' });
        }
    });

    // Pause lotties when tab is backgrounded; resume when foreground.
    document.addEventListener('visibilitychange', () => {
        document.querySelectorAll('.layout-lottie[data-lottie]').forEach((el) => {
            const player = lottiePlayers.get(el);
            if (!player) return;
            if (document.hidden) player.pause();
            else if (deckEl.querySelector('.swiper-slide-active')?.contains(el)) player.play();
        });
    });

    // ============================================================
    // Countdown timers — every node with [data-countdown-target] gets a
    // 1Hz tick that fills [data-countdown-days/hours/minutes/seconds]
    // children. Multiple countdowns on the same page work; we share one
    // setInterval so the page only ticks once per second.
    // ============================================================
    const countdownNodes = document.querySelectorAll('[data-countdown-target]');
    if (countdownNodes.length > 0) {
        const pad = (n) => String(n).padStart(2, '0');
        const tickCountdowns = () => {
            const now = Date.now();
            countdownNodes.forEach((node) => {
                const target = Date.parse(node.dataset.countdownTarget);
                if (Number.isNaN(target)) return;
                let diff = Math.max(0, target - now);
                const days = Math.floor(diff / 86400000); diff -= days * 86400000;
                const hours = Math.floor(diff / 3600000);  diff -= hours * 3600000;
                const minutes = Math.floor(diff / 60000);  diff -= minutes * 60000;
                const seconds = Math.floor(diff / 1000);
                const setVal = (sel, val) => {
                    const el = node.querySelector(`[${sel}]`);
                    if (el) el.textContent = val;
                };
                setVal('data-countdown-days', pad(days));
                setVal('data-countdown-hours', pad(hours));
                setVal('data-countdown-minutes', pad(minutes));
                setVal('data-countdown-seconds', pad(seconds));
            });
        };
        tickCountdowns();
        setInterval(tickCountdowns, 1000);
    }

    // Expose for debugging.
    window._deck = swiper;
    window._lottiePlayers = lottiePlayers;
});
