@php
    /** @var \App\Models\Section $section */
    $images = $section->content['images'] ?? [];
@endphp

<section id="section-gallery" class="section section--gallery" data-section="gallery">
    <div class="section-inner">
        <h2 class="section-title">Galeri</h2>

        @if (! empty($images))
            <div class="gallery-grid">
                @foreach ($images as $img)
                    <figure class="gallery-item">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($img) }}" alt="" loading="lazy">
                    </figure>
                @endforeach
            </div>
        @else
            <p class="gallery-empty">Album foto akan muncul di sini.</p>
        @endif
    </div>
</section>
