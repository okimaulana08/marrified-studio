@php
    /** @var \App\Models\Section $section */
    /** @var \App\Services\Themes\Theme $theme */
    /** @var \App\Models\Invitation $invitation */
    /** @var \App\Models\Guest|null $guest */
    $couple = $invitation->couple;
    $firstEvent = $invitation->events->first();
@endphp

<section id="section-cover" class="section section--cover" data-section="cover">
    <x-theme.section-decorations :theme="$theme" sectionKey="cover" />

    <div class="cover-inner">
        <p class="cover-eyebrow">The Wedding of</p>
        <h1 class="cover-title cover-title--script">
            <span>{{ $couple?->bride_nickname ?? $couple?->bride_name ?? 'Bride' }}</span>
            <em>&amp;</em>
            <span>{{ $couple?->groom_nickname ?? $couple?->groom_name ?? 'Groom' }}</span>
        </h1>

        @if ($firstEvent)
            <p class="cover-date">{{ $firstEvent->date->translatedFormat('d F Y') }}</p>
        @endif

        @if ($guest)
            <div class="guest-greeting">
                <p class="guest-honorific">Kepada Yth.</p>
                <p class="guest-relation">{{ $guest->relation }}</p>
                <p class="guest-name">{{ $guest->name }}</p>
            </div>
        @endif

        <button type="button" class="cover-open-btn" data-cover-open>Open Invitation</button>
    </div>
</section>
