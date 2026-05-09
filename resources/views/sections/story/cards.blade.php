@php
    /** @var \App\Models\Section $section */
    $entries = $section->content['entries'] ?? [];
@endphp

<section id="section-story" class="section section--story section--story-cards" data-section="story">
    <div class="section-inner">
        <h2 class="section-title">Perjalanan Kami</h2>

        @if (! empty($entries))
            <div class="story-cards">
                @foreach ($entries as $entry)
                    @php
                        $year = (string) ($entry['year'] ?? '');
                        $title = (string) ($entry['title'] ?? '');
                        $description = (string) ($entry['description'] ?? '');
                        $photoPath = $entry['photo_path'] ?? null;
                    @endphp
                    <article class="story-card">
                        @if ($photoPath)
                            <figure class="story-card-photo">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($photoPath) }}"
                                     alt="{{ $title }}" loading="lazy">
                            </figure>
                        @endif
                        <div class="story-card-body">
                            <span class="story-card-year">{{ $year }}</span>
                            <h3 class="story-card-title">{{ $title }}</h3>
                            @if ($description !== '')
                                <p class="story-card-text">{{ $description }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <p class="story-empty">Cerita cinta belum diisi.</p>
        @endif
    </div>
</section>
