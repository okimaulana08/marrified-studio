@php
    /** @var \App\Models\Invitation $invitation */
    $events = $invitation->events;
    if ($events->isEmpty()) { return; }
@endphp

<section id="section-event" class="section section--event section--event-ticket" data-section="event">
    <div class="section-inner">
        <h2 class="section-title">Acara</h2>

        <div class="event-ticket-list">
            @foreach ($events as $event)
                @php
                    $day = $event->date->translatedFormat('d');
                    $month = $event->date->translatedFormat('M');
                    $year = $event->date->translatedFormat('Y');
                    $weekday = $event->date->translatedFormat('l');
                    $time = $event->time ? \Carbon\Carbon::parse($event->time)->format('H.i') : null;
                @endphp
                <article class="event-ticket">
                    <div class="event-ticket-stub">
                        <div class="event-ticket-day">{{ $day }}</div>
                        <div class="event-ticket-month">{{ $month }}</div>
                        <div class="event-ticket-year">{{ $year }}</div>
                    </div>

                    <div class="event-ticket-perf" aria-hidden="true">
                        <span></span><span></span><span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span><span></span><span></span>
                    </div>

                    <div class="event-ticket-body">
                        <div class="event-ticket-badge">{{ ucfirst($event->type) }}</div>
                        <h3 class="event-ticket-name">{{ $event->name }}</h3>

                        <div class="event-ticket-meta">
                            <div class="event-ticket-meta-row">
                                <span class="event-ticket-label">Day</span>
                                <span class="event-ticket-value">{{ $weekday }}{{ $time ? ' · '.$time.' WIB' : '' }}</span>
                            </div>
                            <div class="event-ticket-meta-row">
                                <span class="event-ticket-label">Venue</span>
                                <span class="event-ticket-value"><strong>{{ $event->venue_name }}</strong></span>
                            </div>
                            @if ($event->venue_address)
                                <div class="event-ticket-meta-row">
                                    <span class="event-ticket-label">Address</span>
                                    <span class="event-ticket-value">{{ $event->venue_address }}</span>
                                </div>
                            @endif
                        </div>

                        @if ($event->maps_url)
                            <a class="event-ticket-cta" href="{{ $event->maps_url }}" target="_blank" rel="noopener">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                    <circle cx="12" cy="9" r="2.5"/>
                                </svg>
                                <span>Open Maps</span>
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
