@props(['theme', 'sectionKey'])

@php
    /** @var \App\Services\Themes\Theme $theme */
    $secs = $theme->decorationFor($sectionKey);
@endphp

@if (! empty($secs))
    <div class="theme-section-decorations" data-section-deco="{{ $sectionKey }}" aria-hidden="true">
        @if (! empty($secs['frame']['file']))
            <img class="theme-section-frame" src="{{ \App\Support\ThemeAsset::url($theme->slug, $secs['frame']['file']) }}" alt="" loading="lazy" decoding="async">
        @endif

        @if (! empty($secs['tossed']['file']))
            <img
                class="theme-section-tossed theme-section-tossed--{{ $secs['tossed']['slot'] ?? 'top' }}"
                src="{{ \App\Support\ThemeAsset::url($theme->slug, $secs['tossed']['file']) }}"
                style="--tossed-size: {{ $secs['tossed']['size'] ?? '100%' }}; --tossed-opacity: {{ $secs['tossed']['opacity'] ?? 1 }};"
                alt="" loading="lazy" decoding="async">
        @endif

        @if (! empty($secs['scene']['file']))
            <img
                class="theme-section-scene"
                src="{{ \App\Support\ThemeAsset::url($theme->slug, $secs['scene']['file']) }}"
                style="--scene-opacity: {{ $secs['scene']['opacity'] ?? 1 }};"
                alt="" loading="lazy" decoding="async">
        @endif
    </div>
@endif
