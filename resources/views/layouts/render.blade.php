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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $couple?->bride_nickname ?? $couple?->bride_name ?? 'Bride' }} &amp; {{ $couple?->groom_nickname ?? $couple?->groom_name ?? 'Groom' }} — Undangan Pernikahan</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    @php $bunnyFontsUrl = \App\Support\BunnyFonts::url($fonts); @endphp
    @if ($bunnyFontsUrl !== '')
        <link href="{{ $bunnyFontsUrl }}" rel="stylesheet">
    @endif

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
        }
    </style>

    @vite(['resources/css/app.css', 'resources/css/render.css', 'resources/css/theme-anims.css'])
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

        {{-- Swipe hint (only visible on cover) --}}
        <div class="deck-swipe-hint" aria-hidden="true">
            <span class="deck-swipe-hint-icon">↑</span>
            <span class="deck-swipe-hint-text">Geser untuk lanjut</span>
        </div>
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
                <path d="M11 5L6 9H2v6h4l5 4V5z"/><path d="M15.54 8.46a5 5 0 010 7.07"/><path d="M19.07 4.93a10 10 0 010 14.14"/>
            </svg>
            <svg class="bgm-toggle-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                <path d="M11 5L6 9H2v6h4l5 4V5z"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/>
            </svg>
        </button>
    @endif

    @livewireScripts
    @vite('resources/js/render.js')
</body>
</html>
