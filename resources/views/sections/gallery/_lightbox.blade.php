{{-- Shared Alpine lightbox markup. Expects parent <section> to wrap with
     x-data="galleryLightbox(<urls>)" so this partial can reference open/close/
     prev/next/share/download + state. The galleryLightbox() factory is defined
     in render.js. Swipe gestures + pinch-zoom handled by the factory. --}}
<div class="gallery-lightbox" x-show="active" x-cloak x-transition.opacity
     @click.self="close()" role="dialog" aria-modal="true" aria-label="Foto galeri"
     x-on:touchstart.passive="onTouchStart($event)"
     x-on:touchend.passive="onTouchEnd($event)">

    {{-- Top toolbar: download + share + close --}}
    <div class="gl-toolbar">
        <button type="button" class="gl-action" @click.stop="downloadCurrent()" aria-label="Unduh foto">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
            </svg>
        </button>
        <button type="button" class="gl-action" @click.stop="shareCurrent()" aria-label="Bagikan"
                x-show="canShare" x-cloak>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/>
            </svg>
        </button>
        <button type="button" class="gl-action gl-close" @click="close()" aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <button type="button" class="gl-nav gl-prev" @click.stop="prev()"
            x-show="canPrev" x-cloak aria-label="Foto sebelumnya">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6"/>
        </svg>
    </button>

    <button type="button" class="gl-nav gl-next" @click.stop="next()"
            x-show="canNext" x-cloak aria-label="Foto berikutnya">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 6l6 6-6 6"/>
        </svg>
    </button>

    <figure class="gl-figure" @click.stop>
        <img :src="urls[index]" :alt="`Foto ${index + 1} dari ${total}`"
             class="gl-image" style="touch-action: pinch-zoom;">
        <figcaption class="gl-counter">
            <span x-text="index + 1"></span> / <span x-text="total"></span>
        </figcaption>
    </figure>

    {{-- Toast on copy-fallback share --}}
    <div class="gl-toast" x-show="toast" x-cloak x-transition x-text="toastText"></div>
</div>
