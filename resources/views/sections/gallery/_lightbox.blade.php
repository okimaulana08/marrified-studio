{{-- Shared Alpine lightbox markup. Expects parent <section> to wrap with
     x-data="galleryLightbox(<urls>)" so this partial can reference open/close/
     prev/next + state. The galleryLightbox() factory is defined in render.js. --}}
<div class="gallery-lightbox" x-show="active" x-cloak x-transition.opacity
     @click.self="close()" role="dialog" aria-modal="true" aria-label="Foto galeri">

    <button type="button" class="gl-close" @click="close()" aria-label="Tutup">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

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
        <img :src="urls[index]" :alt="`Foto ${index + 1} dari ${total}`" class="gl-image">
        <figcaption class="gl-counter">
            <span x-text="index + 1"></span> / <span x-text="total"></span>
        </figcaption>
    </figure>
</div>
