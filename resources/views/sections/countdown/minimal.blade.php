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

<section id="section-countdown" class="section section--countdown section--countdown-minimal" data-section="countdown">
    <div class="section-inner section-inner--centered">
        <h2 class="section-title section-title--script">{{ $title }}</h2>

        @if ($target)
            <p class="countdown-line" data-countdown-target="{{ $target }}">
                <span class="countdown-line-num" data-countdown-days>--</span><span class="countdown-line-unit">hari</span>
                <span class="countdown-line-num" data-countdown-hours>--</span><span class="countdown-line-unit">jam</span>
                <span class="countdown-line-num" data-countdown-minutes>--</span><span class="countdown-line-unit">menit</span>
                <span class="countdown-line-num" data-countdown-seconds>--</span><span class="countdown-line-unit">detik</span>
            </p>
            @if ($eventLabel)
                <p class="countdown-eyebrow">menuju {{ $eventLabel }}</p>
            @endif
        @else
            <p class="countdown-empty">Tanggal acara belum diisi.</p>
        @endif

        @if ($message)
            <p class="section-lede countdown-message">{{ $message }}</p>
        @endif
    </div>
</section>
