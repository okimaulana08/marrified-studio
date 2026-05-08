@php
    /** @var \App\Models\Invitation $invitation */
    /** @var \App\Services\Themes\Theme $theme */
    /** @var \App\Models\Guest|null $guest */
    $custom = (array) ($invitation->customizations ?? []);
    $palette = array_merge($theme->defaultPalette, (array) ($custom['colors'] ?? []));
    $fonts = array_merge($theme->defaultFonts, (array) ($custom['fonts'] ?? []));
    $sections = $invitation->sections->where('enabled', true)->sortBy('sort_order');
    $coverSection = $sections->firstWhere('type', 'cover');
    $bodySections = $sections->filter(fn ($s) => $s->type !== 'cover');
    $bg = $theme->background();
    $couple = $invitation->couple;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $couple?->bride_nickname ?? $couple?->bride_name ?? 'Bride' }} &amp; {{ $couple?->groom_nickname ?? $couple?->groom_name ?? 'Groom' }} — Undangan Pernikahan</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|petit-formal-script:400|lato:300,400,700&display=swap" rel="stylesheet">

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
    @if ($bg)
        <div class="theme-page-bg" style="background-image: url('{{ \App\Support\ThemeAsset::url($theme->slug, $bg['file']) }}'); opacity: {{ $bg['opacity'] ?? 0.5 }};"></div>
    @endif

    @if ($coverSection)
        <x-render-section :section="$coverSection" :theme="$theme" :invitation="$invitation" :guest="$guest" />
    @endif

    <main id="invitation-main" class="invitation-main">
        @foreach ($bodySections as $section)
            <x-render-section :section="$section" :theme="$theme" :invitation="$invitation" :guest="$guest" />
        @endforeach
    </main>

    @livewireScripts
    @vite('resources/js/render.js')
</body>
</html>
