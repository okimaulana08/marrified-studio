@php
    /** @var \App\Models\Invitation $invitation */
    /** @var \App\Services\Themes\Theme $theme */
    /** @var \App\Models\Guest|null $guest */
    $custom = (array) ($invitation->customizations ?? []);
    $palette = array_merge($theme->defaultPalette, (array) ($custom['colors'] ?? []));
    $fonts = array_merge($theme->defaultFonts, (array) ($custom['fonts'] ?? []));
    $sections = $invitation->sections->where('enabled', true)->sortBy('sort_order')->values();
    $couple = $invitation->couple;
@endphp
@php
    /* ──────────── Open Graph + Twitter Card meta resolution ────────────
     * When the invitation URL is shared on WhatsApp/Twitter/Facebook, the
     * platform fetches these tags to build a preview card. Static for now
     * (no dynamic composite image) — fallback chain:
     *   1. bride photo (most personal) → 2. groom photo → 3. theme preview.
     * All URLs are absolute (URL::to + Storage::disk()->url returns relative
     * path so we prefix with config('app.url') just in case).
     */
    $brideName = $couple?->bride_nickname ?: ($couple?->bride_name ?? 'Bride');
    $groomName = $couple?->groom_nickname ?: ($couple?->groom_name ?? 'Groom');
    $pageTitle = "{$brideName} & {$groomName} — Undangan Pernikahan";

    $firstEvent = $invitation->events->sortBy('sort_order')->first();
    $eventDate = $firstEvent?->date?->translatedFormat('l, d F Y') ?? '';
    $venue = $firstEvent?->venue_name ?? '';
    $pageDescription = trim($eventDate.($venue !== '' ? ' · '.$venue : ''));
    if ($pageDescription === '') {
        $pageDescription = 'Kami mengundang Anda ke pernikahan kami.';
    }

    $ogImage = null;
    foreach ([
        $couple?->bride_photo_path,
        $couple?->groom_photo_path,
    ] as $path) {
        if ($path !== null && \Illuminate\Support\Facades\Storage::disk('invitation_media')->exists($path)) {
            $ogImage = \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($path);
            break;
        }
    }
    if ($ogImage === null) {
        $previewFile = \App\Support\ThemeAsset::findPreview($theme->slug);
        if ($previewFile !== null) {
            $ogImage = \App\Support\ThemeAsset::url($theme->slug, $previewFile);
        }
    }
    // Ensure absolute URL — wa.me / Facebook crawlers won't follow relative.
    if ($ogImage !== null && ! str_starts_with($ogImage, 'http')) {
        $ogImage = url($ogImage);
    }
    $pageUrl = url()->current();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">

    {{-- Open Graph (Facebook, WhatsApp, LinkedIn) --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Marrified Studio">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $pageUrl }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ $pageTitle }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

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
        /* Scale the root font-size so every rem-based size in the render
           (titles, body, cells, etc.) ride the chosen preset together.
           Default browser root = 16px → scaled to 14.4 / 16 / 17.6 / 19.2 */
        html { font-size: calc(100% * var(--fs-scale, 1)); }
    </style>

    @vite(['resources/css/app.css', 'resources/css/render.css', 'resources/css/theme-anims.css'])

    {{-- Admin-authored custom CSS, sanitized again here as defense-in-depth.
         Injected AFTER the bundle so it wins on specificity ties. --}}
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
                    <x-theme.layout-slots :theme="$theme" :page="$section->type" :section="$section" :invitation="$invitation" />
                    <div class="page-content">
                        <x-render-section :section="$section" :theme="$theme" :invitation="$invitation" :guest="$guest" />
                    </div>
                    <button type="button" class="page-scroll-hint" aria-label="Lihat selengkapnya">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                </article>
            @endforeach
        </div>

        {{-- Pagination dots (vertical, right side) --}}
        <div class="deck-pagination" aria-hidden="true"></div>

        {{-- Up / down nav arrows --}}
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

    {{-- Background music: only renders when couple picked a track. Audio.src is
         set lazily via JS after the cover-open click (browser autoplay policy
         requires a user gesture). loop + playsinline for iOS Safari. --}}
    @if ($invitation->musicTrack)
        <audio id="invitation-bgm" preload="none" loop playsinline
               data-src="{{ \Illuminate\Support\Facades\Storage::disk('music_assets')->url($invitation->musicTrack->file_path) }}"
               aria-hidden="true"></audio>
        <button type="button" class="bgm-toggle" data-bgm-toggle aria-label="Matikan musik" hidden>
            <svg class="bgm-toggle-on" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M11 5L6 9H2v6h4l5 4V5z" fill="currentColor"/>
                <path d="M15.54 8.46a5 5 0 010 7.07"/>
                <path d="M19.07 4.93a10 10 0 010 14.14"/>
            </svg>
            <svg class="bgm-toggle-off is-hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M11 5L6 9H2v6h4l5 4V5z"/>
                <line x1="22" y1="8" x2="16" y2="16"/>
                <line x1="16" y1="8" x2="22" y2="16"/>
            </svg>
        </button>
    @endif

    @livewireScripts
    @vite('resources/js/render.js')
</body>
</html>
