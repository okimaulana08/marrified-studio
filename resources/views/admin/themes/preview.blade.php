@php
    /** @var \App\Models\Invitation $invitation */
    /** @var \App\Services\Themes\Theme $theme */
    $palette = $theme->defaultPalette;
    $fonts = $theme->defaultFonts;
    $sections = $invitation->sections->where('enabled', true)->sortBy('sort_order')->values();
    $couple = $invitation->couple;
    $guest = null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Preview — {{ $theme->name }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    @php $bunnyFontsUrl = \App\Support\BunnyFonts::url($fonts); @endphp
    @if ($bunnyFontsUrl !== '')
        <link href="{{ $bunnyFontsUrl }}" rel="stylesheet">
    @endif

    @php
        $scaleMap = ['compact' => 0.9, 'normal' => 1.0, 'spacious' => 1.1, 'showcase' => 1.2];
        $sizeScale = $scaleMap[$fonts['size_scale'] ?? 'normal'] ?? 1.0;
    @endphp
    <style>
        :root {
            --p:  {{ $palette['primary'] ?? '#5d8068' }};
            --a:  {{ $palette['accent'] ?? '#c9a96e' }};
            --a2: {{ $palette['accent2'] ?? '#3d5a47' }};
            --bg: {{ $palette['bg'] ?? '#eef2ea' }};
            --ink: {{ $palette['ink'] ?? '#2a3d31' }};
            --muted: {{ $palette['muted'] ?? '#6f7e72' }};
            --fd: "{{ $fonts['display'] ?? 'Playfair Display' }}", Georgia, serif;
            --fb: "{{ $fonts['body'] ?? 'Lato' }}", system-ui, sans-serif;
            --fs: "{{ $fonts['script'] ?? 'Petit Formal Script' }}", cursive;
            --fs-scale: {{ $sizeScale }};
        }
        /* Scale rem-based font-sizes via root font-size. */
        html { font-size: calc(100% * var(--fs-scale, 1)); }
    </style>

    @vite(['resources/css/app.css', 'resources/css/render.css', 'resources/css/theme-anims.css'])

    {{-- Admin-authored custom CSS, applied after bundle for highest specificity. --}}
    @if (! empty($theme->customCss))
        <style data-theme-custom>{!! \App\Support\CustomCss::sanitize($theme->customCss) !!}</style>
    @endif

    @livewireStyles
</head>
<body class="render-body" data-theme="{{ $theme->slug }}">
    <main class="invitation-deck swiper" id="invitation-main">
        <div class="swiper-wrapper">
            @foreach ($sections as $section)
                <article class="swiper-slide page page--{{ $section->type }}" data-page-type="{{ $section->type }}" data-page-index="{{ $loop->index }}">
                    <x-theme.layout-slots :theme="$theme" :page="$section->type" />
                    <div class="page-content">
                        <x-render-section :section="$section" :theme="$theme" :invitation="$invitation" :guest="$guest" />
                    </div>
                </article>
            @endforeach
        </div>

        <div class="deck-pagination" aria-hidden="true"></div>

        <button type="button" class="deck-nav deck-nav-prev" aria-label="Halaman sebelumnya">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
            </svg>
        </button>
        <button type="button" class="deck-nav deck-nav-next" aria-label="Halaman berikutnya">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

    </main>

    @livewireScripts
    @vite('resources/js/render.js')
</body>
</html>
