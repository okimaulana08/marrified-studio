@php
    /** @var \App\Models\Invitation $invitation */
    $events = $invitation->events;
    if ($events->isEmpty()) { return; }
@endphp

<section id="section-event" class="section section--event section--event-timeline" data-section="event">
    <div class="section-inner">
        <h2 class="section-title">Rangkaian Acara</h2>

        <div class="event-timeline">
            @foreach ($events as $i => $event)
                <article class="event-timeline-item {{ $loop->odd ? 'event-timeline-item--left' : 'event-timeline-item--right' }}">
                    <div class="event-timeline-connector">
                        <div class="event-timeline-dot"></div>
                        @if (! $loop->last)
                            <div class="event-timeline-line"></div>
                        @endif
                    </div>
                    <div class="event-timeline-card">
                        <p class="event-type">{{ ucfirst($event->type) }}</p>
                        <h3 class="event-name">{{ $event->name }}</h3>
                        <p class="event-date">
                            {{ $event->date->translatedFormat('l, d F Y') }}
                            @if ($event->time)
                                <br><span class="event-time">{{ \Carbon\Carbon::parse($event->time)->format('H.i') }} WIB</span>
                            @endif
                        </p>
                        <p class="event-venue">
                            <strong>{{ $event->venue_name }}</strong>
                            @if ($event->venue_address)
                                <br><span class="event-address">{{ $event->venue_address }}</span>
                            @endif
                        </p>
                        @if ($event->maps_url)
                            <a class="event-maps-btn" href="{{ $event->maps_url }}" target="_blank" rel="noopener">Open Maps</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
