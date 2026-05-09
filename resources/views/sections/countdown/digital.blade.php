@php
    /** @var \App\Models\Section $section */
    /** @var \App\Models\Invitation $invitation */
    $content = (array) ($section->content ?? []);
    $title = $content['title'] ?? 'Hitung Mundur';
    $message = $content['message'] ?? '';
    $event = $invitation->events->first();
    $target = $event?->date?->copy()->setTime(0, 0)->toIso8601String();
    $eventLabel = $event?->name ?: '';
@endphp

<section id="section-countdown" class="section section--countdown section--countdown-digital" data-section="countdown">
    <div class="section-inner section-inner--centered">
        <h2 class="section-title">{{ $title }}</h2>

        @if ($eventLabel)
            <p class="countdown-eyebrow">menuju {{ $eventLabel }}</p>
        @endif

        @if ($target)
            <div class="countdown-grid" data-countdown-target="{{ $target }}">
                <div class="countdown-cell">
                    <span class="countdown-value" data-countdown-days>--</span>
                    <span class="countdown-label">Hari</span>
                </div>
                <div class="countdown-cell">
                    <span class="countdown-value" data-countdown-hours>--</span>
                    <span class="countdown-label">Jam</span>
                </div>
                <div class="countdown-cell">
                    <span class="countdown-value" data-countdown-minutes>--</span>
                    <span class="countdown-label">Menit</span>
                </div>
                <div class="countdown-cell">
                    <span class="countdown-value" data-countdown-seconds>--</span>
                    <span class="countdown-label">Detik</span>
                </div>
            </div>
        @else
            <p class="countdown-empty">Tanggal acara belum diisi.</p>
        @endif

        @if ($message)
            <p class="section-lede countdown-message">{{ $message }}</p>
        @endif
    </div>
</section>
