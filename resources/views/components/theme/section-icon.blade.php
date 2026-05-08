@props(['theme', 'sectionKey', 'size' => '48px'])

@php
    /** @var \App\Services\Themes\Theme $theme */
    $iconFile = $theme->decorationFor($sectionKey)['icon']['file'] ?? null;
    $isSvg = $iconFile && str_ends_with($iconFile, '.svg');
    $svgPath = $isSvg ? \App\Support\ThemeAsset::publicPath($theme->slug, $iconFile) : null;
@endphp

@if ($iconFile)
    @if ($isSvg && $svgPath && file_exists($svgPath))
        <div class="theme-section-icon" style="width: {{ $size }}; height: {{ $size }};">
            {!! file_get_contents($svgPath) !!}
        </div>
    @else
        <img
            class="theme-section-icon"
            src="{{ \App\Support\ThemeAsset::url($theme->slug, $iconFile) }}"
            style="width: {{ $size }}; height: {{ $size }};"
            alt="" loading="lazy" decoding="async">
    @endif
@endif
