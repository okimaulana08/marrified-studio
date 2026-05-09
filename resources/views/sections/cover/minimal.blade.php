@php
    /** @var \App\Models\Section $section */
    /** @var \App\Services\Themes\Theme $theme */
    /** @var \App\Models\Invitation $invitation */
    /** @var \App\Models\Guest|null $guest */
    $couple = $invitation->couple;
    $firstEvent = $invitation->events->first();
@endphp

<section id="section-cover" class="section section--cover section--cover-minimal" data-section="cover">
    <div class="cover-inner cover-inner--centered">
        @if ($firstEvent)
            <p class="cover-eyebrow cover-eyebrow--date">{{ $firstEvent->date->translatedFormat('d · F · Y') }}</p>
        @endif

        <h1 class="cover-title cover-title--script">
            <span>{{ $couple?->bride_nickname ?? $couple?->bride_name ?? 'Bride' }}</span>
            <span class="cover-ampersand">&amp;</span>
            <span>{{ $couple?->groom_nickname ?? $couple?->groom_name ?? 'Groom' }}</span>
        </h1>

        <p class="cover-eyebrow">The Wedding of</p>

        @if ($guest)
            <div class="guest-greeting guest-greeting--minimal">
                <p class="guest-name">{{ $guest->name }}</p>
            </div>
        @endif

        <button type="button" class="cover-open-btn cover-open-btn--minimal" data-cover-open>
            Open Invitation
        </button>
    </div>
</section>
