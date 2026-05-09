@props(['theme', 'page' => null])

@php
    /** @var \App\Services\Themes\Theme $theme */
    $bg = $theme->background($page);
    $slots = $theme->slots($page);
    $lottie = $theme->lottie($page);
    $hasAny = $bg !== null || $slots !== [] || $lottie !== null;
@endphp

@if ($hasAny)
    <div class="layout-slots" aria-hidden="true">
        @if ($bg !== null)
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
            @endphp
            @php
                $loop = (string) ($config['anim_loop'] ?? '');
            @endphp
            <div class="layout-slot layout-slot--{{ $slotName }} {{ $animClass }}"
                 data-slot="{{ $slotName }}"
                 @if ($anim !== '') data-anim="{{ $anim }}" @endif
                 @style($animStyles)>
                <img src="{{ $url }}" alt="" loading="lazy" decoding="async"
                     @if ($loop !== '') data-anim-loop="{{ $loop }}" @endif>
            </div>
        @endforeach

        @if ($lottie !== null)
            <div class="layout-lottie layout-lottie--{{ $lottie['placement'] ?? 'center' }} layout-lottie--size-{{ $lottie['size'] ?? 'medium' }}"
                 data-lottie="{{ \App\Support\ThemeAsset::url($theme->slug, $lottie['file']) }}"
                 data-lottie-loop="{{ ($lottie['loop'] ?? true) ? '1' : '0' }}"></div>
        @endif
    </div>
@endif
