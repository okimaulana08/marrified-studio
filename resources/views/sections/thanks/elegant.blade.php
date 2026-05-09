@php
    /** @var \App\Models\Section $section */
    /** @var \App\Services\Themes\Theme $theme */
    /** @var \App\Models\Invitation $invitation */
    $content = (array) ($section->content ?? []);
    $title = $content['title'] ?? 'Terima Kasih';
    $message = $content['message'] ?? 'Atas kehadiran dan doa restu yang telah diberikan, kami mengucapkan terima kasih.';
    $signature = $content['signature'] ?? '';
    $photoPath = $content['photo_path'] ?? null;
    $couple = $invitation->couple;
    $coupleNames = trim(($couple?->bride_name ?? '').' & '.($couple?->groom_name ?? ''), ' &');
    $photoUrl = $photoPath ? \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($photoPath) : null;
@endphp

<section id="section-thanks" class="section section--thanks section--thanks-elegant" data-section="thanks">
    @if ($photoUrl)
        <div class="thanks-bg" style="background-image: url('{{ $photoUrl }}')" aria-hidden="true"></div>
        <div class="thanks-bg-veil" aria-hidden="true"></div>
    @endif

    <div class="section-inner section-inner--centered thanks-elegant-inner">
        <div class="thanks-divider" aria-hidden="true">
            <span></span><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.18L12 21z"/></svg><span></span>
        </div>

        <h2 class="section-title section-title--script">{{ $title }}</h2>

        <p class="thanks-message">{{ $message }}</p>

        <div class="thanks-divider" aria-hidden="true"><span></span><span></span></div>

        <p class="thanks-signature">
            {{ $signature !== '' ? $signature : 'Kami yang berbahagia,' }}
        </p>
        <p class="thanks-couple">{{ $coupleNames !== '' ? $coupleNames : 'Bride & Groom' }}</p>
    </div>
</section>
