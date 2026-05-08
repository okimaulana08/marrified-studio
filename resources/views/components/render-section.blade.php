@props(['section', 'theme', 'invitation', 'guest' => null])

@php
    /** @var \App\Models\Section $section */
    /** @var \App\Services\Themes\Theme $theme */
    /** @var \App\Models\Invitation $invitation */
    $type = $section->type;
    $candidates = array_filter([
        $section->variant,
        $theme->defaultSectionVariants[$type] ?? null,
        'default',
    ]);
    $resolved = null;
    foreach ($candidates as $variant) {
        $candidate = "sections.{$type}.{$variant}";
        if (view()->exists($candidate)) {
            $resolved = $candidate;
            break;
        }
    }
@endphp

@if ($resolved)
    @include($resolved, [
        'section' => $section,
        'theme' => $theme,
        'invitation' => $invitation,
        'guest' => $guest,
    ])
@endif
