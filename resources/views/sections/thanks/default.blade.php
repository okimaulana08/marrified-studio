@php
    /** @var \App\Models\Section $section */
    /** @var \App\Services\Themes\Theme $theme */
    /** @var \App\Models\Invitation $invitation */
    $content = (array) ($section->content ?? []);
    $title = $content['title'] ?? 'Terima Kasih';
    $message = $content['message'] ?? 'Atas kehadiran dan doa restu yang telah diberikan, kami mengucapkan terima kasih.';
    $signature = $content['signature'] ?? '';
    $couple = $invitation->couple;
    $coupleNames = trim(($couple?->bride_name ?? '').' & '.($couple?->groom_name ?? ''), ' &');
@endphp

<section id="section-thanks" class="section section--thanks section--thanks-default" data-section="thanks">
    <div class="section-inner section-inner--centered">
        <h2 class="section-title section-title--script">{{ $title }}</h2>

        <p class="thanks-message">{{ $message }}</p>

        <p class="thanks-signature">
            {{ $signature !== '' ? $signature : 'Kami yang berbahagia,' }}
        </p>
        <p class="thanks-couple">{{ $coupleNames !== '' ? $coupleNames : 'Bride & Groom' }}</p>
    </div>
</section>
