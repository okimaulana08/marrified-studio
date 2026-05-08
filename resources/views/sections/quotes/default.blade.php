@php
    /** @var \App\Models\Invitation $invitation */
    $religious = $invitation->religious_text ?? null;
    if (! $religious || empty($religious['ayat'] ?? null)) {
        return;
    }
@endphp

<section id="section-quotes" class="section section--quotes" data-section="quotes">
    <x-theme.section-decorations :theme="$theme" sectionKey="quotes" />

    <div class="section-inner section-inner--centered">
        <x-theme.section-icon :theme="$theme" sectionKey="quotes" size="56px" />

        <p class="ayat-arabic" lang="ar" dir="rtl">{{ $religious['ayat'] }}</p>
        @if (! empty($religious['translation'] ?? null))
            <p class="ayat-translation">"{{ $religious['translation'] }}"</p>
        @endif
        @if (! empty($religious['source'] ?? null))
            <p class="ayat-source">— {{ $religious['source'] }} —</p>
        @endif
    </div>
</section>
