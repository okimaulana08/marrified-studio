<div x-data="{
    tab: sessionStorage.getItem('themeEditorTab') || 'basic',
    viewport: 'mobile',
    hasDirty: false,
}"
     x-init="$watch('tab', v => sessionStorage.setItem('themeEditorTab', v))"
     x-on:theme-saved.window="hasDirty = false; window.dispatchEvent(new CustomEvent('refresh-preview-manual'))"
     x-on:theme-discarded.window="hasDirty = false"
     x-on:before-unload.window="if(hasDirty) { $event.preventDefault(); $event.returnValue = '' }">

    {{-- Header --}}
    <div class="flex items-end justify-between mb-6">
        <div class="flex-1 min-w-0">
            @if ($isNew)
                <p class="text-xs uppercase tracking-[0.25em] text-emerald-400/70 font-semibold mb-2 flex items-center gap-2">
                    <span class="inline-block w-6 h-px bg-emerald-400/50"></span>
                    Theme Studio
                </p>
                <h1 class="font-display text-4xl font-bold tracking-display text-gradient leading-tight">
                    New Theme
                </h1>
                <p class="text-sm text-white/40 mt-2">Buat tema dari awal — lengkapi info dasar lalu simpan</p>
            @else
                <p class="text-xs uppercase tracking-[0.25em] text-emerald-400/70 font-semibold mb-2 flex items-center gap-2">
                    <span class="inline-block w-6 h-px bg-emerald-400/50"></span>
                    Editing Theme
                </p>
                <h1 class="font-display text-4xl font-bold tracking-display text-gradient leading-tight truncate">
                    {{ $basic->name ?: $slug }}
                </h1>
                <p class="text-sm font-mono text-emerald-400/60 mt-1.5">{{ $slug }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
            @if (!$isNew)
                <a href="{{ route('admin.themes.preview', $slug) }}" target="_blank"
                   class="btn-ghost flex items-center gap-1.5 text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Full Preview
                </a>
            @endif
            <a href="{{ route('admin.themes.index') }}" class="btn-ghost text-xs">
                ← Semua Tema
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if ($flashMessage)
        <div wire:key="flash-{{ $previewKey }}-{{ $flashType }}"
             x-data="{ show: true }" x-show="show" x-cloak x-transition
             x-init="setTimeout(() => show = false, 4000)"
             class="mb-4 px-4 py-3 rounded-xl text-sm font-medium glass-strong
                    {{ $flashType === 'error' ? 'border-red-400/30 text-red-300' : ($flashType === 'info' ? 'border-blue-400/30 text-blue-300' : 'border-emerald-400/30 text-emerald-300') }}">
            {{ $flashMessage }}
        </div>
    @endif

    {{-- Main: split layout — fixed viewport height so preview pane stays put
         while the form pane scrolls internally via overflow-y-auto. --}}
    <div class="flex gap-5 h-[calc(100vh-180px)]">

        {{-- Left: form panel --}}
        <div class="w-full {{ !$isNew ? 'lg:w-[480px] xl:w-[540px] flex-shrink-0' : '' }} flex flex-col gap-4">

            {{-- Tab nav --}}
            <div class="glass rounded-2xl p-1.5 flex gap-0.5 overflow-x-auto">
                @foreach ([
                    'basic'       => ['label' => 'Info',      'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'palette'     => ['label' => 'Palette',   'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
                    'fonts'       => ['label' => 'Fonts',     'icon' => 'M4 6h16M4 12h8m-8 6h16'],
                    'variants'    => ['label' => 'Variants',  'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                    'layout'      => ['label' => 'Layout',    'icon' => 'M4 5a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM14 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4z'],
                    'assets'      => ['label' => 'Assets',    'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ] as $tabKey => $tabMeta)
                    <button type="button" x-on:click="tab = '{{ $tabKey }}'"
                            :class="tab === '{{ $tabKey }}'
                                ? 'text-white shadow-inner'
                                : 'text-white/40 hover:text-white/70'"
                            x-bind:style="tab === '{{ $tabKey }}'
                                ? 'background: linear-gradient(135deg, rgba(232,62,140,0.25) 0%, rgba(255,122,133,0.18) 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.1), 0 0 16px -4px rgba(232,62,140,0.4); border: 1px solid rgba(232,62,140,0.4);'
                                : 'background: transparent; border: 1px solid transparent;'"
                            class="relative flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tabMeta['icon'] }}"/>
                        </svg>
                        {{ $tabMeta['label'] }}
                        @if (isset($dirty[$tabKey]))
                            <span class="absolute top-0 right-0 w-2 h-2 bg-amber-400 rounded-full ring-2 ring-slate-900 pulse-amber"></span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Tab panels --}}
            <div class="flex-1 glass rounded-2xl p-6 overflow-y-auto relative"
                 x-on:change.capture="hasDirty = true"
                 x-on:input.capture="hasDirty = true"
                 x-on:click.capture="hasDirty = true">
                {{-- Subtle highlight on top --}}
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-1/3 h-px"
                     style="background: linear-gradient(90deg, transparent, rgba(232,62,140,0.4), transparent);"></div>

                <div x-show="tab === 'basic'" x-cloak class="fade-up">
                    @include('livewire.admin.partials.tab-basic')
                </div>
                <div x-show="tab === 'palette'" x-cloak class="fade-up">
                    @include('livewire.admin.partials.tab-palette')
                </div>
                <div x-show="tab === 'fonts'" x-cloak class="fade-up">
                    @include('livewire.admin.partials.tab-fonts')
                </div>
                <div x-show="tab === 'variants'" x-cloak class="fade-up">
                    @include('livewire.admin.partials.tab-variants')
                </div>
                <div x-show="tab === 'layout'" x-cloak class="fade-up">
                    @include('livewire.admin.partials.tab-layout')
                </div>
                <div x-show="tab === 'assets'" x-cloak class="fade-up">
                    @include('livewire.admin.partials.tab-assets')
                </div>
            </div>

            {{-- Save / Discard footer --}}
            <div class="glass rounded-2xl px-5 py-3 flex items-center gap-3">
                <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        class="btn-primary">
                    <span wire:loading.remove wire:target="save" class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $isNew ? 'Buat Tema' : 'Simpan' }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>

                <button wire:click="discardChanges" class="btn-ghost"
                        x-show="hasDirty && {{ $isNew ? 'false' : 'true' }}" x-cloak>
                    Batalkan
                </button>

                <div x-show="hasDirty" x-cloak
                     class="flex items-center gap-2 ml-auto px-3 py-1.5 rounded-full bg-amber-400/10 border border-amber-400/20">
                    <span class="relative flex w-1.5 h-1.5">
                        <span class="absolute inline-flex w-full h-full bg-amber-400 rounded-full opacity-75 animate-ping"></span>
                        <span class="relative inline-flex rounded-full w-1.5 h-1.5 bg-amber-400"></span>
                    </span>
                    <span class="text-[10px] uppercase tracking-widest text-amber-300 font-bold">
                        Perubahan belum disimpan
                    </span>
                </div>
            </div>
        </div>

        {{-- Right: live preview iframe (only when editing) --}}
        @if (!$isNew)
            <div class="hidden lg:flex flex-col flex-1 min-w-0">

                {{-- Preview toolbar --}}
                <div class="glass rounded-2xl px-4 py-2.5 mb-3 flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="relative flex w-1.5 h-1.5">
                            <span class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping" style="background-color: #10b981 !important;"></span>
                            <span class="relative inline-flex rounded-full w-1.5 h-1.5" style="background-color: #10b981 !important;"></span>
                        </span>
                        <span class="text-[10px] uppercase tracking-widest font-bold" style="color: rgba(52,211,153,0.85) !important;">Live</span>
                    </div>

                    <div class="h-3 w-px bg-white/10"></div>

                    {{-- Viewport buttons --}}
                    <div class="flex gap-0.5 bg-white/5 rounded-xl p-0.5 border border-white/8">
                        @foreach ([
                            'mobile'  => ['label' => 'Phone',  'width' => '390', 'icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'],
                            'tablet'  => ['label' => 'Tablet', 'width' => '768', 'icon' => 'M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                            'desktop' => ['label' => 'Web',    'width' => '100%', 'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        ] as $vpKey => $vpMeta)
                            <button x-on:click="viewport = '{{ $vpKey }}'"
                                    :class="viewport === '{{ $vpKey }}' ? 'bg-emerald-500/25 text-emerald-200 border-emerald-400/30' : 'text-white/40 hover:text-white/70 border-transparent'"
                                    class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg transition-all border">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $vpMeta['icon'] }}"/>
                                </svg>
                                {{ $vpMeta['label'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        <span x-text="viewport === 'mobile' ? '390 × ' : viewport === 'tablet' ? '768 × ' : '100% × '"
                              class="text-xs text-white/30 font-mono"></span>
                        <span class="text-xs text-white/30 font-mono">auto</span>
                        <button type="button"
                                x-on:click="window.dispatchEvent(new CustomEvent('refresh-preview-manual'))"
                                class="flex items-center gap-1 text-xs text-white/40 hover:text-white transition-colors px-2 py-1 glass-sm rounded-lg">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>

                {{-- iframe container with device frame on phone/tablet --}}
                <div class="flex-1 flex justify-center items-start overflow-auto glass rounded-2xl p-4 relative">
                    {{-- Decorative grid in preview area --}}
                    <div class="absolute inset-0 pointer-events-none opacity-30"
                         style="background-image:
                             linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                             linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
                             background-size: 24px 24px;
                             mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);"></div>

                    <div x-bind:style="viewport === 'mobile' ? 'width:390px' : viewport === 'tablet' ? 'width:768px' : 'width:100%'"
                         class="h-full transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] mx-auto relative z-10"
                         x-data="{ baseSrc: @js(route('admin.themes.preview', $slug)) }"
                         x-init="
                            window.addEventListener('refresh-preview-manual', () => {
                                $refs.preview.src = baseSrc + '?v=' + Date.now();
                            });
                         ">
                        <iframe
                            x-ref="preview"
                            wire:ignore
                            src="{{ route('admin.themes.preview', $slug) }}?v={{ $previewKey }}"
                            class="w-full h-full rounded-2xl bg-white shadow-2xl"
                            style="box-shadow: 0 24px 60px -12px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.1);"
                            loading="lazy"
                            title="Preview: {{ $slug }}">
                        </iframe>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
