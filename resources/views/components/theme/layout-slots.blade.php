@props(['theme', 'page' => null, 'section' => null, 'invitation' => null])

@php
    /** @var \App\Services\Themes\Theme $theme */
    /** @var \App\Models\Section|null $section */
    /** @var \App\Models\Invitation|null $invitation */

    // Per-section background override (couple photo / gallery / upload) wins
    // over the theme manifest default. Returns null when this section uses
    // the theme bg as-is.
    $bgOverride = null;
    if ($section !== null && $invitation !== null) {
        $bgOverride = $section->resolveBgOverride($invitation);
    }
    $bg = $bgOverride !== null ? null : $theme->background($page);

    $slots = $theme->slots($page);
    $lottie = $theme->lottie($page);
    $hasAny = $bg !== null || $bgOverride !== null || $slots !== [] || $lottie !== null;
@endphp

@if ($hasAny)
    <div class="layout-slots" aria-hidden="true">
        @if ($bgOverride !== null)
            <div class="layout-bg layout-bg--{{ $bgOverride['fit'] }}"
                 @style([
                     'background-image: url('.$bgOverride['file_url'].')',
                     'opacity: '.$bgOverride['opacity'],
                 ])></div>
            @if (($bgOverride['darken'] ?? 0) > 0.001)
                <div class="layout-bg-dim" @style(['opacity: '.$bgOverride['darken']])></div>
            @endif
        @elseif ($bg !== null)
            <div class="layout-bg layout-bg--{{ $bg['fit'] ?? 'cover' }}"
                 @style([
                     'background-image: url('.\App\Support\ThemeAsset::url($theme->slug, $bg['file']).')',
                     'opacity: '.($bg['opacity'] ?? 0.5),
                 ])></div>
        @endif

        @foreach ($slots as $slotName => $config)
            @php
                $url = \App\Support\ThemeAsset::url($theme->slug, $config['file']);
                $anim = (string) ($config['anim_in'] ?? '');
                $animClass = $anim !== '' ? 'anim-'.$anim : '';
                $duration = (int) ($config['duration_ms'] ?? 0);
                $delay = (int) ($config['delay_ms'] ?? 0);
                $animStyles = [];
                if ($duration > 0) {
                    $animStyles[] = "animation-duration: {$duration}ms";
                }
                if ($delay > 0) {
                    $animStyles[] = "animation-delay: {$delay}ms";
                }
                $loop = (string) ($config['anim_loop'] ?? '');
                // Static visual transforms — applied to a dedicated middle wrapper
                // so they don't fight the outer anim_in (translateY/X) or inner
                // anim_loop transforms.
                $scale = (float) ($config['scale'] ?? 1.0);
                $offsetX = (int) ($config['offset_x'] ?? 0);
                $offsetY = (int) ($config['offset_y'] ?? 0);
                $rotate = (int) ($config['rotate'] ?? 0);
                $hasTransform = abs($scale - 1.0) > 0.001 || $offsetX !== 0 || $offsetY !== 0 || $rotate !== 0;
                // Order matters: translate FIRST (in original element coords), then rotate around centre,
                // then scale. That way the offset feels predictable regardless of rotation.
                $transformStyle = $hasTransform
                    ? "transform: translate({$offsetX}px, {$offsetY}px) rotate({$rotate}deg) scale(".round($scale, 3).');'
                    : '';
            @endphp
            <div class="layout-slot layout-slot--{{ $slotName }} {{ $animClass }}"
                 data-slot="{{ $slotName }}"
                 @if ($anim !== '') data-anim="{{ $anim }}" @endif
                 @style($animStyles)>
                <div class="layout-slot-transform" style="{{ $transformStyle }}">
                    <img src="{{ $url }}" alt="" loading="lazy" decoding="async"
                         @if ($loop !== '') data-anim-loop="{{ $loop }}" @endif>
                </div>
            </div>
        @endforeach

        @if ($lottie !== null)
            <div class="layout-lottie layout-lottie--{{ $lottie['placement'] ?? 'center' }} layout-lottie--size-{{ $lottie['size'] ?? 'medium' }}"
                 data-lottie="{{ \App\Support\ThemeAsset::url($theme->slug, $lottie['file']) }}"
                 data-lottie-loop="{{ ($lottie['loop'] ?? true) ? '1' : '0' }}"></div>
        @endif
    </div>
@endif
