@php
    /** @var \App\Models\Section $section */
    $entries = $section->content['entries'] ?? [];
@endphp

<section id="section-story" class="section section--story" data-section="story">
    <div class="section-inner section-inner--centered">
        <h2 class="section-title">Cerita Cinta Kami</h2>

        @if (! empty($entries))
            <ol class="story-timeline" aria-label="Love story timeline">
                @foreach ($entries as $i => $entry)
                    @php
                        $year = (string) ($entry['year'] ?? '');
                        $title = (string) ($entry['title'] ?? '');
                        $description = (string) ($entry['description'] ?? '');
                        $photoPath = $entry['photo_path'] ?? null;
                        $side = $i % 2 === 0 ? 'left' : 'right';
                    @endphp
                    <li class="story-entry story-entry--{{ $side }}">
                        <span class="story-entry-dot" aria-hidden="true"></span>
                        <div class="story-entry-card">
                            <span class="story-entry-year">{{ $year }}</span>
                            <h3 class="story-entry-title">{{ $title }}</h3>
                            @if ($photoPath)
                                <figure class="story-entry-photo">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($photoPath) }}"
                                         alt="{{ $title }}" loading="lazy">
                                </figure>
                            @endif
                            @if ($description !== '')
                                <p class="story-entry-text">{{ $description }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="story-empty">Cerita cinta belum diisi.</p>
        @endif
    </div>
</section>
