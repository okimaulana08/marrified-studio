<div>
    {{-- Flash message --}}
    @if ($flashMessage)
        <div x-data="{ show: true }" x-show="show" x-cloak x-transition
             x-init="setTimeout(() => show = false, 4000)"
             class="mb-5 px-4 py-3 rounded-xl text-sm font-medium glass-strong
                    {{ $flashType === 'error' ? 'border-red-400/30 text-red-300' : 'border-emerald-400/30 text-emerald-300' }}">
            {{ $flashMessage }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-end justify-between mb-8">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-emerald-400/70 font-semibold mb-2 flex items-center gap-2">
                <span class="inline-block w-6 h-px bg-emerald-400/50"></span>
                Theme Studio
            </p>
            <h1 class="font-display text-4xl font-bold tracking-display text-gradient leading-tight">
                Themes Library
            </h1>
            <p class="text-sm text-white/40 mt-2">Kelola tema undangan dari filesystem — atomic writes, no DB</p>
        </div>
        <a href="{{ route('admin.themes.create') }}" class="btn-primary flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Theme
        </a>
    </div>

    {{-- Search + stats --}}
    <div class="flex items-center justify-between mb-6 gap-4">
        <div class="relative max-w-xs flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search"
                   type="text"
                   placeholder="Cari tema..."
                   class="admin-input w-full pl-9 pr-4 py-2 text-sm">
        </div>
        <div class="flex items-center gap-3 text-xs text-white/40">
            <span>{{ $themes->count() }} tema</span>
            <span class="w-px h-3 bg-white/15"></span>
            <span>{{ $themes->where('theme.isPremium', true)->count() }} premium</span>
        </div>
    </div>

    {{-- Theme grid --}}
    @if ($themes->isEmpty())
        <div class="text-center py-24 glass rounded-3xl">
            <div class="w-20 h-20 rounded-2xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center mx-auto mb-5"
                 style="box-shadow: 0 8px 32px -8px rgba(232,62,140,0.3);">
                <svg class="w-10 h-10 text-emerald-400/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="font-display text-2xl text-white/80 tracking-display">Belum ada tema</p>
            <p class="text-sm text-white/30 mt-1.5">Buat tema pertama atau duplicate yang sudah ada.</p>
            <a href="{{ route('admin.themes.create') }}" class="btn-primary inline-flex items-center gap-1.5 mt-6">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Tema Pertama
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach ($themes as $i => $row)
                @php
                    $theme = $row['theme'];
                    $delayMs = $i * 50;
                @endphp
                <div @style(["animation-delay: {$delayMs}ms"])
                     class="glass card-lift rounded-2xl overflow-hidden group fade-up">

                    {{-- Preview thumbnail --}}
                    <div class="relative overflow-hidden" style="aspect-ratio: 4 / 3;">
                        <div class="absolute inset-0 bg-gradient-to-br from-white/5 via-transparent to-emerald-500/5"></div>
                        @php $previewFile = \App\Support\ThemeAsset::findPreview($theme->slug); @endphp
                        @if ($previewFile)
                            <img src="{{ \App\Support\ThemeAsset::url($theme->slug, $previewFile) }}"
                                 alt="{{ $theme->name }}"
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            {{-- Vignette --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent pointer-events-none"></div>
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-14 h-14 text-white/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        @if ($theme->isPremium)
                            <span class="absolute top-3 right-3 px-2 py-0.5 text-xs font-bold rounded-full text-amber-900"
                                  style="background: linear-gradient(135deg, #fcd34d 0%, #f59e0b 100%);
                                         box-shadow: 0 2px 8px -2px rgba(251,191,36,0.6), inset 0 1px 0 rgba(255,255,255,0.3);">
                                Premium
                            </span>
                        @endif

                        {{-- Hover overlay with quick actions --}}
                        <div class="absolute inset-0 flex items-end justify-center pb-3 opacity-0 group-hover:opacity-100 transition-opacity bg-gradient-to-t from-black/60 to-transparent">
                            <a href="{{ route('admin.themes.edit', $theme->slug) }}"
                               class="px-4 py-1.5 bg-white/95 text-emerald-700 text-xs font-bold rounded-full shadow-xl hover:bg-white transition-colors">
                                Edit Tema →
                            </a>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h3 class="font-display font-semibold text-lg text-white truncate tracking-display leading-tight">
                                {{ $theme->name }}
                            </h3>
                        </div>
                        <p class="text-[11px] text-emerald-400/60 font-mono">{{ $theme->slug }}</p>

                        {{-- Palette swatches --}}
                        @if ($theme->defaultPalette)
                            <div class="flex items-center gap-2 mt-3">
                                <div class="flex -space-x-1.5">
                                    @foreach (array_slice($theme->defaultPalette, 0, 5) as $color)
                                        <div class="w-5 h-5 rounded-full ring-2 ring-slate-900 transition-transform group-hover:scale-110" title="{{ $color }}" @style(["background-color: $color"])></div>
                                    @endforeach
                                </div>
                                <span class="text-[10px] text-white/30 font-mono">{{ $row['assetCount'] }} assets</span>
                            </div>
                        @endif

                        {{-- Actions --}}
                        <div class="flex gap-1.5 mt-4">
                            <a href="{{ route('admin.themes.edit', $theme->slug) }}"
                               class="flex-1 flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl
                                      bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300
                                      border border-emerald-400/20 transition-all">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </a>
                            <a href="{{ $row['previewUrl'] }}" target="_blank"
                               class="flex items-center justify-center w-9 h-9 glass-sm text-white/40 hover:text-white rounded-xl transition-all"
                               title="Preview tab baru">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                            <button wire:click="openCloneModal('{{ $theme->slug }}')"
                                    class="flex items-center justify-center w-9 h-9 glass-sm text-white/40 hover:text-white rounded-xl transition-all"
                                    title="Duplicate">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Clone Modal --}}
    @if ($showCloneModal)
        <div class="fixed inset-0 bg-black/70 backdrop-blur-md z-50 flex items-center justify-center p-4 fade-up"
             style="animation-duration: 0.25s">
            <div class="glass-strong rounded-2xl w-full max-w-md p-7 shadow-2xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-400/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-display text-xl font-semibold text-white tracking-display">Duplicate Tema</h2>
                        <p class="text-xs text-white/40">Asset, palette, dan dekorasi disalin penuh.</p>
                    </div>
                </div>

                <div class="my-5 px-3 py-2 glass-subtle rounded-lg">
                    <p class="text-xs text-white/40">Sumber</p>
                    <p class="font-mono text-sm text-emerald-400">{{ $cloneSourceSlug }}</p>
                </div>

                <label class="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Slug baru</label>
                <input wire:model="cloneTargetSlug" type="text"
                       class="admin-input w-full px-3 py-2.5 text-sm font-mono
                              @error('cloneTargetSlug') border-red-400/50 @enderror"
                       placeholder="contoh: watercolor-coral" autofocus>
                @error('cloneTargetSlug')
                    <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                @enderror
                <p class="text-xs text-white/25 mt-1.5">Lowercase, huruf/angka/tanda hubung, 3-50 karakter.</p>

                <div class="flex gap-2 mt-6 justify-end">
                    <button wire:click="closeCloneModal" class="btn-ghost">Batal</button>
                    <button wire:click="confirmClone" wire:loading.attr="disabled" class="btn-primary">
                        <span wire:loading.remove wire:target="confirmClone">Duplicate Sekarang</span>
                        <span wire:loading wire:target="confirmClone">Menduplikasi...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
