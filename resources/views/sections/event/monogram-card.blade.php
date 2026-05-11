@php
    /** @var \App\Models\Invitation $invitation */
    $events = $invitation->events;
    if ($events->isEmpty()) { return; }
@endphp

<section id="section-event" class="section section--event section--event-monogram" data-section="event">
    <div class="section-inner">
        <h2 class="section-title">Acara</h2>

        <div class="event-monogram-list">
            @foreach ($events as $event)
                @php
                    $day = $event->date->translatedFormat('d');
                    $monthFull = $event->date->translatedFormat('F');
                    $year = $event->date->translatedFormat('Y');
                    $weekday = $event->date->translatedFormat('l');
                    $time = $event->time ? \Carbon\Carbon::parse($event->time)->format('H.i') : null;
                @endphp
                <article class="event-monogram-card">
                    <div class="event-monogram-corner event-monogram-corner--tl" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M2 2 L2 18 M2 2 L18 2" stroke="currentColor" stroke-width="1"/>
                            <path d="M4 4 Q14 4 14 14" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.6"/>
                        </svg>
                    </div>
                    <div class="event-monogram-corner event-monogram-corner--tr" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M38 2 L38 18 M38 2 L22 2" stroke="currentColor" stroke-width="1"/>
                            <path d="M36 4 Q26 4 26 14" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.6"/>
                        </svg>
                    </div>
                    <div class="event-monogram-corner event-monogram-corner--bl" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M2 38 L2 22 M2 38 L18 38" stroke="currentColor" stroke-width="1"/>
                            <path d="M4 36 Q14 36 14 26" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.6"/>
                        </svg>
                    </div>
                    <div class="event-monogram-corner event-monogram-corner--br" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M38 38 L38 22 M38 38 L22 38" stroke="currentColor" stroke-width="1"/>
                            <path d="M36 36 Q26 36 26 26" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.6"/>
                        </svg>
                    </div>

                    <p class="event-monogram-eyebrow">{{ ucfirst($event->type) }}</p>
                    <h3 class="event-monogram-name">{{ $event->name }}</h3>

                    <div class="event-monogram-divider" aria-hidden="true">
                        <span class="event-monogram-divider-line"></span>
                        <svg class="event-monogram-divider-leaf" viewBox="0 0 24 16" fill="none">
                            <path d="M2 8 Q8 2 12 8 Q16 14 22 8" stroke="currentColor" stroke-width="1" fill="none"/>
                            <circle cx="12" cy="8" r="1.2" fill="currentColor"/>
                        </svg>
                        <span class="event-monogram-divider-line"></span>
                    </div>

                    <div class="event-monogram-date">
                        <div class="event-monogram-date-day">{{ $day }}</div>
                        <div class="event-monogram-date-meta">
                            <span class="event-monogram-weekday">{{ $weekday }}</span>
                            <span class="event-monogram-month">{{ $monthFull }} {{ $year }}</span>
                            @if ($time)
                                <span class="event-monogram-time">{{ $time }} WIB</span>
                            @endif
                        </div>
                    </div>

                    <div class="event-monogram-venue">
                        <strong>{{ $event->venue_name }}</strong>
                        @if ($event->venue_address)
                            <span class="event-monogram-address">{{ $event->venue_address }}</span>
                        @endif
                    </div>

                    @if ($event->maps_url)
                        <a class="event-monogram-cta" href="{{ $event->maps_url }}" target="_blank" rel="noopener">Open Maps →</a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
