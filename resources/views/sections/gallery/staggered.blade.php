@php
    /** @var \App\Models\Section $section */
    $images = $section->content['images'] ?? [];
    $urls = array_map(
        fn ($p) => \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($p),
        $images,
    );
@endphp

<section id="section-gallery" class="section section--gallery section--gallery-staggered"
         data-section="gallery"
         x-data="galleryLightbox(@js($urls))"
         x-on:keydown.escape.window="close()"
         x-on:keydown.left.window="prev()"
         x-on:keydown.right.window="next()">
    <div class="section-inner">
        <h2 class="section-title">Galeri</h2>

        @if (! empty($urls))
            {{-- CSS multi-column gives a masonry/Instagram-staggered effect:
                 each photo keeps its native aspect ratio and flows into columns
                 with `break-inside: avoid` to prevent splits. --}}
            <div class="gallery-staggered">
                @foreach ($urls as $i => $url)
                    <button type="button" class="gallery-staggered-item" @click="open({{ $i }})">
                        <img src="{{ $url }}" alt="" loading="lazy">
                    </button>
                @endforeach
            </div>

            @include('sections.gallery._lightbox')
        @else
            <p class="gallery-empty">Album foto akan muncul di sini.</p>
        @endif
    </div>
</section>
