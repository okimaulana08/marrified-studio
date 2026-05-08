@php
    /** @var \App\Models\Invitation $invitation */
    $events = $invitation->events;
    if ($events->isEmpty()) { return; }
@endphp

<section id="section-event" class="section section--event" data-section="event">
    <x-theme.section-decorations :theme="$theme" sectionKey="event" />

    <div class="section-inner">
        <x-theme.section-icon :theme="$theme" sectionKey="event" size="48px" />
        <h2 class="section-title">Acara</h2>

        <div class="event-list">
            @foreach ($events as $event)
                <article class="event-card">
                    <p class="event-type">{{ ucfirst($event->type) }}</p>
                    <h3 class="event-name">{{ $event->name }}</h3>
                    <p class="event-date">{{ $event->date->translatedFormat('l, d F Y') }}{{ $event->time ? ', '.\Carbon\Carbon::parse($event->time)->format('H.i').' WIB' : '' }}</p>
                    <p class="event-venue">
                        <strong>{{ $event->venue_name }}</strong>
                        @if ($event->venue_address)
                            <br><span class="event-address">{{ $event->venue_address }}</span>
                        @endif
                    </p>
                    @if ($event->maps_url)
                        <a class="event-maps-btn" href="{{ $event->maps_url }}" target="_blank" rel="noopener">Open Maps</a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
