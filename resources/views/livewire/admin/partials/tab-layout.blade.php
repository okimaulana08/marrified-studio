@php
    // Resolve which slot map and wire:model prefix to use based on activePage.
    $isPageMode = $layout->activePage !== '';
    $modelPrefix = $isPageMode ? "layout.pages.{$layout->activePage}" : 'layout';
    $currentSlots = $isPageMode
        ? ($layout->pages[$layout->activePage]['slots'] ?? [])
        : $layout->slots;
@endphp

<div x-data="{
        focused: null,
        focus(slot) {
            this.focused = slot;
            this.$nextTick(() => {
                const el = document.querySelector('[data-slot-row=' + slot + ']');
                if (el) {
                    // Auto-expand the focused row so the user lands directly in the
                    // editor without an extra click on the chevron.
                    if (window.Alpine) {
                        const alpineData = window.Alpine.$data(el);
                        if (alpineData && typeof alpineData.open === 'boolean') {
                            alpineData.open = true;
                        }
                    }
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    el.classList.add('is-flash');
                    setTimeout(() => el.classList.remove('is-flash'), 1700);
                }
            });
        }
     }"
     class="space-y-5">

    {{-- Section header --}}
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM14 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Layout per Page</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Klik dot di peta untuk fokus ke slot</p>
        </div>
    </div>

    @if (empty($availableAssets))
        <div class="p-4 bg-amber-400/10 border border-amber-400/20 rounded-xl text-sm text-amber-300/80">
            Belum ada asset. Upload file di tab <strong>Assets</strong> dulu.
        </div>
    @endif

    {{-- ============== PAGE SELECTOR ============== --}}
    <div class="border-t border-white/8 pt-4">
        <p class="text-[10px] text-white/40 uppercase tracking-widest mb-2 flex items-center gap-2">
            <span class="w-1 h-3 bg-emerald-400/60 rounded-full"></span>
            Editing scope
        </p>
        <div class="flex flex-wrap gap-1">
            <button type="button"
                    wire:click="$set('layout.activePage', '')"
                    @class([
                        'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all border',
                        'bg-emerald-500/25 text-emerald-200 border-emerald-400/40' => ! $isPageMode,
                        'glass-sm text-white/55 hover:text-white border-transparent' => $isPageMode,
                    ])>
                Default <span class="text-white/40 font-normal">(semua page)</span>
            </button>
            @foreach (\App\Livewire\Admin\Forms\LayoutForm::sectionTypes() as $type)
                @php
                    $pageSlotCount = collect($layout->pages[$type]['slots'] ?? [])
                        ->filter(fn ($s) => ($s['file'] ?? '') !== '')
                        ->count();
                @endphp
                <button type="button"
                        wire:click="$set('layout.activePage', '{{ $type }}')"
                        @class([
                            'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all border flex items-center gap-1.5 capitalize',
                            'bg-emerald-500/25 text-emerald-200 border-emerald-400/40' => $layout->activePage === $type,
                            'glass-sm text-white/55 hover:text-white border-transparent' => $layout->activePage !== $type,
                        ])>
                    {{ $type }}
                    @if ($pageSlotCount > 0)
                        <span class="px-1 py-px bg-emerald-300/30 rounded text-[9px] font-mono normal-case tracking-normal">{{ $pageSlotCount }}</span>
                    @endif
                </button>
            @endforeach
        </div>
        @if ($isPageMode)
            <p class="text-[11px] text-emerald-300/70 mt-2.5">
                Slot di-set di sini akan <strong>override default</strong> hanya untuk page <span class="font-mono text-white">{{ $layout->activePage }}</span>. Background &amp; Lottie tetap dari Default.
            </p>
        @endif
    </div>

    {{-- ============== VISUAL PAGE MAP ============== --}}
    <div class="border-t border-white/8 pt-4">
        <div class="grid grid-cols-2 gap-4 items-start">
            <div>
                <h3 class="text-xs font-semibold text-white/60 uppercase tracking-widest mb-2 flex items-center gap-2">
                    <span class="w-1 h-3 bg-emerald-400/60 rounded-full"></span>
                    Page Map
                </h3>
                <div class="layout-map">
                    @foreach (\App\Services\Themes\ThemeRegistry::SLOT_KEYS as $slotKey)
                        @php
                            $isFilled = ($currentSlots[$slotKey]['file'] ?? '') !== '';
                            $assignedFile = $currentSlots[$slotKey]['file'] ?? '';
                            $titleAttr = $slotLabels[$slotKey] . ($isFilled ? " · {$assignedFile}" : ' (kosong)');
                        @endphp
                        <button type="button"
                                x-on:click="focus('{{ $slotKey }}')"
                                :class="focused === '{{ $slotKey }}' ? 'is-focused' : ''"
                                @class([
                                    'lmap-zone',
                                    "lmap-zone--{$slotKey}",
                                    'is-filled' => $isFilled,
                                ])
                                title="{{ $titleAttr }}"
                                aria-label="{{ $titleAttr }}">
                        </button>
                    @endforeach
                </div>
                <div class="flex items-center justify-center gap-3 mt-3 text-[10px] text-white/40">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-white/20 border border-white/30"></span>
                        Kosong
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" style="background: linear-gradient(135deg, #10b981, #14b8a6); box-shadow: 0 0 4px rgba(232,62,140,0.5);"></span>
                        Terisi
                    </span>
                </div>
            </div>

            <div class="space-y-2">
                <h3 class="text-xs font-semibold text-white/60 uppercase tracking-widest mb-2 flex items-center gap-2">
                    <span class="w-1 h-3 bg-emerald-400/60 rounded-full"></span>
                    Status
                </h3>
                @php
                    $totalFilled = collect(\App\Services\Themes\ThemeRegistry::SLOT_KEYS)
                        ->filter(fn ($k) => ($currentSlots[$k]['file'] ?? '') !== '')
                        ->count();
                    $totalSlots = count(\App\Services\Themes\ThemeRegistry::SLOT_KEYS);
                @endphp
                <div class="glass-sm rounded-xl px-3 py-2.5">
                    <p class="text-[11px] text-white/40 uppercase tracking-wider">Slots</p>
                    <p class="text-2xl font-display font-semibold text-white leading-none mt-1">{{ $totalFilled }}<span class="text-white/30 text-base">/{{ $totalSlots }}</span></p>
                </div>
                <div class="glass-sm rounded-xl px-3 py-2.5">
                    <p class="text-[11px] text-white/40 uppercase tracking-wider">Background</p>
                    <p class="text-sm text-white/80 mt-1 truncate">
                        @if ($layout->bgFile)
                            <span class="text-emerald-400">●</span> {{ $layout->bgFile }}
                        @else
                            <span class="text-white/30">— belum ada —</span>
                        @endif
                    </p>
                </div>
                <div class="glass-sm rounded-xl px-3 py-2.5">
                    <p class="text-[11px] text-white/40 uppercase tracking-wider">Lottie</p>
                    <p class="text-sm text-white/80 mt-1 truncate">
                        @if ($layout->lottieFile)
                            <span class="text-emerald-400">●</span> {{ $layout->lottieFile }}
                        @else
                            <span class="text-white/30">— belum ada —</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ============== BACKGROUND ============== --}}
    <div class="border-t border-white/8 pt-4">
        <h3 class="text-xs font-semibold text-white/60 uppercase tracking-widest mb-3 flex items-center gap-2">
            <span class="w-1 h-3 bg-emerald-400/60 rounded-full"></span>
            Background
        </h3>
        <div class="glass-sm rounded-xl p-4 grid grid-cols-12 gap-3">
            <div class="col-span-5">
                <label class="block text-[11px] text-white/40 mb-1.5">File</label>
                <div class="relative">
                    <select wire:model.live="layout.bgFile" class="admin-select w-full px-2 py-1.5 pr-7 text-xs">
                        <option value="">— tidak ada —</option>
                        @foreach ($availableAssets as $asset)
                            <option value="{{ $asset }}">{{ $asset }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
            <div class="col-span-3">
                <label class="block text-[11px] text-white/40 mb-1.5">Fit</label>
                <div class="relative">
                    <select wire:model.live="layout.bgFit" class="admin-select w-full px-2 py-1.5 pr-7 text-xs capitalize">
                        @foreach ($bgFitOptions as $fit)
                            <option value="{{ $fit }}">{{ $fit }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
            <div class="col-span-3">
                <label class="block text-[11px] text-white/40 mb-1.5">
                    Opacity <span class="text-white/60">{{ number_format($layout->bgOpacity, 2) }}</span>
                </label>
                <input type="range" wire:model.live="layout.bgOpacity" min="0" max="1" step="0.05" class="w-full">
            </div>
            <div class="col-span-1 flex items-end">
                @if ($layout->bgFile)
                    @php $bgExt = strtolower(pathinfo($layout->bgFile, PATHINFO_EXTENSION)); @endphp
                    @if (in_array($bgExt, ['webp','png','jpg','jpeg']))
                        <img src="{{ \App\Support\ThemeAsset::url($slug, $layout->bgFile) }}"
                             class="w-9 h-9 object-cover rounded-lg ring-1 ring-white/15" alt="">
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- ============== SLOT CARDS ============== --}}
    <div class="border-t border-white/8 pt-4">
        <h3 class="text-xs font-semibold text-white/60 uppercase tracking-widest mb-3 flex items-center gap-2">
            <span class="w-1 h-3 bg-emerald-400/60 rounded-full"></span>
            11 Slot Posisi
        </h3>

        <div class="space-y-3">
            @foreach ($slotGroups as $groupName => $slotKeys)
                <div class="glass-sm rounded-xl">
                    <div class="px-4 py-2.5 border-b border-white/8 flex items-center justify-between">
                        <span class="text-xs font-semibold text-white/70">{{ $groupName }}</span>
                        @php
                            $filled = collect($slotKeys)->filter(fn ($k) => ($currentSlots[$k]['file'] ?? '') !== '')->count();
                        @endphp
                        @if ($filled > 0)
                            <span class="px-1.5 py-0.5 bg-emerald-400/15 text-emerald-400 text-[10px] rounded-full font-medium border border-emerald-400/20">
                                {{ $filled }}/{{ count($slotKeys) }}
                            </span>
                        @endif
                    </div>

                    <div class="p-3 space-y-2">
                        @foreach ($slotKeys as $slotKey)
                            @php
                                $hasFile = ($currentSlots[$slotKey]['file'] ?? '') !== '';
                                $currentAnim = $currentSlots[$slotKey]['anim_in'] ?? '';
                                $currentLoop = $currentSlots[$slotKey]['anim_loop'] ?? '';
                                $currentScale = (float) ($currentSlots[$slotKey]['scale'] ?? 1.0);
                                $currentOffsetX = (int) ($currentSlots[$slotKey]['offset_x'] ?? 0);
                                $currentOffsetY = (int) ($currentSlots[$slotKey]['offset_y'] ?? 0);
                                $currentRotate = (int) ($currentSlots[$slotKey]['rotate'] ?? 0);
                                $hasTransform = abs($currentScale - 1.0) > 0.001 || $currentOffsetX !== 0 || $currentOffsetY !== 0 || $currentRotate !== 0;
                                $defaultFile = $isPageMode ? ($layout->slots[$slotKey]['file'] ?? '') : '';
                                $isInheriting = $isPageMode && ! $hasFile && $defaultFile !== '';
                                $isOverriding = $isPageMode && $hasFile;
                            @endphp
                            <div data-slot-row="{{ $slotKey }}"
                                 x-data="{ open: false }"
                                 class="slot-row rounded-lg transition-colors px-2 py-2 -mx-2">
                                {{-- Header row: state dot + label + file picker + expand chevron --}}
                                <div class="grid grid-cols-12 gap-2 items-center">
                                    <div class="col-span-1 flex justify-center">
                                        @if ($hasFile)
                                            <span class="w-2 h-2 rounded-full" style="background: linear-gradient(135deg, #10b981, #14b8a6); box-shadow: 0 0 6px rgba(232,62,140,0.5);" title="Set"></span>
                                        @elseif ($isInheriting)
                                            <span class="w-2 h-2 rounded-full bg-amber-400/40 border border-amber-400/60" title="Inherits from default"></span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-white/15 border border-white/25"></span>
                                        @endif
                                    </div>
                                    <div class="col-span-3">
                                        <p class="text-xs text-white/80 leading-tight flex items-center gap-1.5">
                                            {{ $slotLabels[$slotKey] }}
                                            @if ($isOverriding)
                                                <span class="text-[8px] px-1 py-px rounded bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 uppercase tracking-wider font-mono">over</span>
                                            @elseif ($isInheriting)
                                                <span class="text-[8px] px-1 py-px rounded bg-amber-400/15 text-amber-300/80 border border-amber-400/20 uppercase tracking-wider font-mono">inh</span>
                                            @endif
                                        </p>
                                        <p class="text-[10px] text-white/30 font-mono">{{ $slotKey }}</p>
                                        @if ($isInheriting)
                                            <p class="text-[10px] text-amber-300/60 truncate font-mono">← {{ $defaultFile }}</p>
                                        @endif
                                    </div>
                                    <div class="col-span-6">
                                        <div class="relative">
                                            <select wire:model.live="{{ $modelPrefix }}.slots.{{ $slotKey }}.file"
                                                    class="admin-select w-full px-2 py-1.5 pr-6 text-xs">
                                                <option value="">@if ($isPageMode){{ $defaultFile !== '' ? '— inherit dari default —' : '— kosong —' }}@else— kosong —@endif</option>
                                                @foreach ($availableAssets as $asset)
                                                    <option value="{{ $asset }}">{{ $asset }}</option>
                                                @endforeach
                                            </select>
                                            <svg class="absolute right-1.5 top-1/2 -translate-y-1/2 w-3 h-3 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex justify-center">
                                        @if ($hasFile)
                                            @php
                                                $sFile = $currentSlots[$slotKey]['file'];
                                                $sExt = strtolower(pathinfo($sFile, PATHINFO_EXTENSION));
                                                $svgPath = resource_path("themes/{$slug}/assets/{$sFile}");
                                            @endphp
                                            @if ($sExt === 'svg' && file_exists($svgPath))
                                                <div class="h-7 w-7 flex items-center justify-center text-white/60">
                                                    {!! file_get_contents($svgPath) !!}
                                                </div>
                                            @elseif (in_array($sExt, ['webp','png','jpg','jpeg']))
                                                <img src="{{ \App\Support\ThemeAsset::url($slug, $sFile) }}"
                                                     class="h-7 w-7 object-cover rounded ring-1 ring-white/20" alt="">
                                            @endif
                                        @endif
                                    </div>
                                    <div class="col-span-1 flex justify-end">
                                        @if ($hasFile)
                                            <button type="button" x-on:click="open = !open"
                                                    :title="open ? 'Tutup pengaturan' : 'Buka pengaturan animasi & posisi'"
                                                    class="w-7 h-7 flex items-center justify-center rounded-md text-white/45 hover:text-white hover:bg-white/[0.06] transition-colors">
                                                <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Summary badges (visible when collapsed) — at-a-glance status --}}
                                @if ($hasFile)
                                    <div class="mt-1 pl-[8.33%] flex flex-wrap items-center gap-1.5" x-show="!open">
                                        @if ($currentAnim)
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/10 border border-emerald-400/20 text-emerald-300/85 font-mono">
                                                anim: {{ $currentAnim }}
                                            </span>
                                        @endif
                                        @if ($currentLoop)
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-cyan-500/10 border border-cyan-400/20 text-cyan-300/85 font-mono">
                                                loop: {{ $currentLoop }}
                                            </span>
                                        @endif
                                        @if ($hasTransform)
                                            @php
                                                $transformSummary = round($currentScale, 2).'× · '.$currentOffsetX.','.$currentOffsetY.'px';
                                                if ($currentRotate !== 0) {
                                                    $transformSummary .= ' · '.$currentRotate.'°';
                                                }
                                            @endphp
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/10 border border-amber-400/20 text-amber-300/85 font-mono">
                                                ⤢ {{ $transformSummary }}
                                            </span>
                                        @endif
                                        @if (! $currentAnim && ! $currentLoop && ! $hasTransform)
                                            <span class="text-[10px] text-white/30 italic">tanpa pengaturan tambahan</span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Anim preview picker (only when expanded) --}}
                                @if ($hasFile)
                                    <div class="mt-2.5 pl-[8.33%]" x-show="open" x-collapse>
                                        <p class="text-[10px] text-white/35 mb-1.5 uppercase tracking-wider flex items-center gap-1.5">
                                            Anim Masuk
                                            @if ($currentAnim)
                                                <span class="text-emerald-400/80 font-mono normal-case tracking-normal">{{ $currentAnim }}</span>
                                            @else
                                                <span class="text-white/30 normal-case">tidak ada</span>
                                            @endif
                                        </p>
                                        <div class="anim-pick-grid">
                                            {{-- "No animation" option --}}
                                            <button type="button"
                                                    wire:click="$set('{{ $modelPrefix }}.slots.{{ $slotKey }}.anim_in', '')"
                                                    @class(['anim-pick-box', 'is-active' => $currentAnim === ''])
                                                    title="Tanpa animasi">
                                                <div class="anim-pick-none">
                                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="9"/>
                                                        <line x1="5" y1="19" x2="19" y2="5"/>
                                                    </svg>
                                                </div>
                                            </button>

                                            @foreach ($animOptions as $anim)
                                                <button type="button"
                                                        wire:click="$set('{{ $modelPrefix }}.slots.{{ $slotKey }}.anim_in', '{{ $anim }}')"
                                                        @class([
                                                            'anim-pick-box',
                                                            "ap--{$anim}",
                                                            'is-active' => $currentAnim === $anim,
                                                        ])
                                                        title="{{ $anim }}">
                                                    <div class="anim-pick-dot"></div>
                                                    <span class="anim-pick-label">{{ str_replace('-', ' ', $anim) }}</span>
                                                </button>
                                            @endforeach
                                        </div>

                                        {{-- Anim Loop picker (idle animation while slide is active) --}}
                                        @php
                                            $currentLoop = (string) ($currentSlots[$slotKey]['anim_loop'] ?? '');
                                        @endphp
                                        <div class="mt-2.5">
                                            <p class="text-[10px] text-white/35 mb-1.5 uppercase tracking-wider flex items-center gap-1.5">
                                                Anim Loop
                                                @if ($currentLoop)
                                                    <span class="text-emerald-400/80 font-mono normal-case tracking-normal">{{ $currentLoop }}</span>
                                                @else
                                                    <span class="text-white/30 normal-case">tidak ada</span>
                                                @endif
                                            </p>
                                            <div class="flex flex-wrap gap-1">
                                                <button type="button"
                                                        wire:click="$set('{{ $modelPrefix }}.slots.{{ $slotKey }}.anim_loop', '')"
                                                        @class([
                                                            'px-2.5 py-1 text-[10px] font-semibold rounded-lg border transition-all',
                                                            'bg-emerald-500/25 text-emerald-200 border-emerald-400/40' => $currentLoop === '',
                                                            'glass-sm text-white/55 hover:text-white border-transparent' => $currentLoop !== '',
                                                        ])>
                                                    Tidak ada
                                                </button>
                                                @foreach ($animLoopOptions as $loopOpt)
                                                    <button type="button"
                                                            wire:click="$set('{{ $modelPrefix }}.slots.{{ $slotKey }}.anim_loop', '{{ $loopOpt }}')"
                                                            @class([
                                                                'px-2.5 py-1 text-[10px] font-semibold rounded-lg border transition-all capitalize',
                                                                'bg-emerald-500/25 text-emerald-200 border-emerald-400/40' => $currentLoop === $loopOpt,
                                                                'glass-sm text-white/55 hover:text-white border-transparent' => $currentLoop !== $loopOpt,
                                                            ])>
                                                        {{ $loopOpt }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>

                                        @if ($currentAnim !== '')
                                            @php
                                                $duration = (int) ($currentSlots[$slotKey]['duration_ms'] ?? 0);
                                                $delay    = (int) ($currentSlots[$slotKey]['delay_ms'] ?? 0);
                                            @endphp
                                            <div class="mt-2.5 grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="flex items-center justify-between text-[10px] text-white/35 mb-1 uppercase tracking-wider">
                                                        <span>Duration</span>
                                                        <span class="text-emerald-400/80 font-mono normal-case">{{ $duration > 0 ? $duration.'ms' : 'auto' }}</span>
                                                    </label>
                                                    <input type="range" wire:model.live="{{ $modelPrefix }}.slots.{{ $slotKey }}.duration_ms"
                                                           min="0" max="{{ \App\Services\Themes\ThemeRegistry::DURATION_MAX_MS }}" step="100" class="w-full">
                                                </div>
                                                <div>
                                                    <label class="flex items-center justify-between text-[10px] text-white/35 mb-1 uppercase tracking-wider">
                                                        <span>Delay</span>
                                                        <span class="text-emerald-400/80 font-mono normal-case">{{ $delay > 0 ? $delay.'ms' : 'auto' }}</span>
                                                    </label>
                                                    <input type="range" wire:model.live="{{ $modelPrefix }}.slots.{{ $slotKey }}.delay_ms"
                                                           min="0" max="{{ \App\Services\Themes\ThemeRegistry::DELAY_MAX_MS }}" step="50" class="w-full">
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Scale + Offset position controls --}}
                                        <div class="mt-3 pt-3 border-t border-white/8">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <p class="text-[10px] uppercase tracking-wider text-white/35 flex items-center gap-1.5">
                                                    Posisi &amp; Ukuran
                                                    @if ($hasTransform)
                                                        <span class="font-mono text-amber-300/85 normal-case tracking-normal">
                                                            {{ $transformSummary ?? (round($currentScale, 2).'× · '.$currentOffsetX.', '.$currentOffsetY.'px'.($currentRotate !== 0 ? ' · '.$currentRotate.'°' : '')) }}
                                                        </span>
                                                    @else
                                                        <span class="text-white/25 normal-case">default</span>
                                                    @endif
                                                </p>
                                                @if ($hasTransform)
                                                    <button type="button"
                                                            wire:click="$set('{{ $modelPrefix }}.slots.{{ $slotKey }}.scale', 1)"
                                                            x-on:click="$wire.set('{{ $modelPrefix }}.slots.{{ $slotKey }}.offset_x', 0); $wire.set('{{ $modelPrefix }}.slots.{{ $slotKey }}.offset_y', 0); $wire.set('{{ $modelPrefix }}.slots.{{ $slotKey }}.rotate', 0)"
                                                            class="text-[10px] text-white/40 hover:text-white/70 underline-offset-2 hover:underline">Reset</button>
                                                @endif
                                            </div>
                                            <div class="grid grid-cols-2 gap-x-3 gap-y-2.5">
                                                <div>
                                                    <label class="text-[10px] text-white/40 mb-1 block">Scale</label>
                                                    <div class="flex items-center gap-1.5">
                                                        <input type="range"
                                                               wire:model.live.debounce.150ms="{{ $modelPrefix }}.slots.{{ $slotKey }}.scale"
                                                               min="0.3" max="2.5" step="0.05" class="flex-1 min-w-0">
                                                        <input type="number"
                                                               wire:model.live.debounce.500ms="{{ $modelPrefix }}.slots.{{ $slotKey }}.scale"
                                                               min="0.1" max="5" step="0.05"
                                                               class="admin-input w-14 px-1.5 py-1 text-[11px] font-mono text-center"
                                                               title="Skala (×)">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="text-[10px] text-white/40 mb-1 block">Rotate (°)</label>
                                                    <div class="flex items-center gap-1.5">
                                                        <input type="range"
                                                               wire:model.live.debounce.150ms="{{ $modelPrefix }}.slots.{{ $slotKey }}.rotate"
                                                               min="-180" max="180" step="1" class="flex-1 min-w-0">
                                                        <input type="number"
                                                               wire:model.live.debounce.500ms="{{ $modelPrefix }}.slots.{{ $slotKey }}.rotate"
                                                               step="1"
                                                               class="admin-input w-14 px-1.5 py-1 text-[11px] font-mono text-center"
                                                               title="Putar — minus = berlawanan jarum jam, plus = searah">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="text-[10px] text-white/40 mb-1 block">Offset X (px)</label>
                                                    <div class="flex items-center gap-1.5">
                                                        <input type="range"
                                                               wire:model.live.debounce.150ms="{{ $modelPrefix }}.slots.{{ $slotKey }}.offset_x"
                                                               min="-200" max="200" step="1" class="flex-1 min-w-0">
                                                        <input type="number"
                                                               wire:model.live.debounce.500ms="{{ $modelPrefix }}.slots.{{ $slotKey }}.offset_x"
                                                               step="1"
                                                               class="admin-input w-14 px-1.5 py-1 text-[11px] font-mono text-center"
                                                               title="Geser horizontal — minus = kiri, plus = kanan">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="text-[10px] text-white/40 mb-1 block">Offset Y (px)</label>
                                                    <div class="flex items-center gap-1.5">
                                                        <input type="range"
                                                               wire:model.live.debounce.150ms="{{ $modelPrefix }}.slots.{{ $slotKey }}.offset_y"
                                                               min="-200" max="200" step="1" class="flex-1 min-w-0">
                                                        <input type="number"
                                                               wire:model.live.debounce.500ms="{{ $modelPrefix }}.slots.{{ $slotKey }}.offset_y"
                                                               step="1"
                                                               class="admin-input w-14 px-1.5 py-1 text-[11px] font-mono text-center"
                                                               title="Geser vertikal — minus = atas, plus = bawah">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ============== LOTTIE ============== --}}
    <div class="border-t border-white/8 pt-4">
        <h3 class="text-xs font-semibold text-white/60 uppercase tracking-widest mb-3 flex items-center gap-2">
            <span class="w-1 h-3 bg-emerald-400/60 rounded-full"></span>
            Animasi Lottie
        </h3>
        <div class="glass-sm rounded-xl p-4 space-y-3">
            <div class="grid grid-cols-12 gap-3">
                <div class="col-span-5">
                    <label class="block text-[11px] text-white/40 mb-1.5">File (.json)</label>
                    <div class="relative">
                        <select wire:model.live="layout.lottieFile" class="admin-select w-full px-2 py-1.5 pr-7 text-xs">
                            <option value="">— tidak ada —</option>
                            @foreach ($availableAssets as $asset)
                                @if (str_ends_with(strtolower($asset), '.json'))
                                    <option value="{{ $asset }}">{{ $asset }}</option>
                                @endif
                            @endforeach
                        </select>
                        <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div class="col-span-3">
                    <label class="block text-[11px] text-white/40 mb-1.5">Placement</label>
                    <div class="relative">
                        <select wire:model="layout.lottiePlacement" class="admin-select w-full px-2 py-1.5 pr-7 text-xs capitalize">
                            @foreach ($lottiePlacementOptions as $placement)
                                <option value="{{ $placement }}">{{ $placement }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="block text-[11px] text-white/40 mb-1.5">Size</label>
                    <div class="relative">
                        <select wire:model="layout.lottieSize" class="admin-select w-full px-2 py-1.5 pr-7 text-xs capitalize">
                            @foreach ($lottieSizeOptions as $size)
                                <option value="{{ $size }}">{{ $size }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div class="col-span-2 flex items-center pt-5">
                    <label class="flex items-center gap-2 text-xs text-white/60 cursor-pointer">
                        <input type="checkbox" wire:model="layout.lottieLoop"
                               class="w-3.5 h-3.5 rounded bg-white/5 border-white/15 accent-emerald-500">
                        Loop
                    </label>
                </div>
            </div>
            <p class="text-[11px] text-white/30">Size: <strong>small</strong> 80–160px · <strong>medium</strong> 120–240px · <strong>large</strong> 200–400px · <strong>xlarge</strong> 280–560px · <strong>full</strong> menutupi seluruh halaman.</p>
        </div>
    </div>
</div>
