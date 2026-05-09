<div class="space-y-5">
    {{-- Section header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
            </div>
            <div>
                <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Color Palette</h2>
                <p class="text-[11px] text-white/40 mt-0.5">6 warna inti untuk seluruh tema</p>
            </div>
        </div>

        {{-- Preset picker --}}
        <div x-data="{ open: false }" class="relative">
            <button x-on:click="open = !open"
                    class="flex items-center gap-1.5 px-3 py-1.5 glass-sm text-white/50 hover:text-white text-xs font-semibold rounded-xl transition-all">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                Presets
                <svg x-bind:class="open ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak x-transition.opacity x-on:click.outside="open = false"
                 class="absolute right-0 top-full mt-2 w-60 glass-strong rounded-2xl shadow-2xl z-30 overflow-hidden p-1">
                @foreach ($palettePresets as $key => $preset)
                    <button wire:click="applyPalettePreset('{{ $key }}')"
                            x-on:click="open = false"
                            class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-white/8 text-left transition-colors rounded-xl group">
                        <div class="flex gap-0.5 flex-shrink-0 transition-transform group-hover:scale-110">
                            @foreach (['primary', 'accent', 'accent2', 'bg', 'ink'] as $pk)
                                <div class="w-3.5 h-7 rounded-sm ring-1 ring-white/15" @style(["background-color: {$preset[$pk]}"])></div>
                            @endforeach
                        </div>
                        <span class="text-sm text-white/80 font-medium">{{ $preset['name'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="border-t border-white/8 pt-4">
        {{-- Color swatches preview --}}
        <div class="relative overflow-hidden rounded-2xl mb-5"
             style="background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.02) 100%); border: 1px solid rgba(255,255,255,0.08);">
            <div class="absolute inset-0 opacity-30" @style(['background: radial-gradient(circle at top right, ' . $palette->accent . ' 0%, transparent 60%)'])></div>
            <div class="relative grid grid-cols-6 gap-1 p-3">
                @foreach ([
                    'primary' => $palette->primary,
                    'accent'  => $palette->accent,
                    'accent2' => $palette->accent2,
                    'bg'      => $palette->bg,
                    'ink'     => $palette->ink,
                    'muted'   => $palette->muted,
                ] as $label => $color)
                    <div class="flex flex-col items-center gap-1.5">
                        <div class="w-full h-12 rounded-xl ring-1 ring-white/15 shadow-lg transition-transform hover:scale-105 hover:-translate-y-0.5 cursor-pointer" title="{{ $color }}" @style(["background-color: $color"])></div>
                        <span class="text-[9px] text-white/40 uppercase tracking-wider font-semibold">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Color inputs --}}
        <div class="space-y-2.5">
            @foreach ([
                ['primary', 'Primary',    'Heading & button utama',     '#10b981'],
                ['accent',  'Accent',     'Highlight & ornamen',        '#f59e0b'],
                ['accent2', 'Accent 2',   'Aksen sekunder',             '#6366f1'],
                ['bg',      'Background', 'Latar halaman',              '#ffffff'],
                ['ink',     'Ink',        'Teks utama',                 '#1f2937'],
                ['muted',   'Muted',      'Teks sekunder',              '#9ca3af'],
            ] as [$key, $label, $hint, $defaultColor])
                <div class="glass-sm rounded-xl px-3 py-2 flex items-center gap-3">
                    <div class="relative flex-shrink-0">
                        <input type="color" wire:model.live="palette.{{ $key }}"
                               class="w-9 h-9 rounded-lg cursor-pointer p-0 border-0 bg-transparent">
                        <div class="absolute inset-0 rounded-lg ring-1 ring-white/20 pointer-events-none"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white/90 leading-tight">{{ $label }}</p>
                        <p class="text-[11px] text-white/35">{{ $hint }}</p>
                    </div>
                    <input type="text" wire:model.live="palette.{{ $key }}"
                           class="admin-input w-24 px-2 py-1.5 text-xs font-mono uppercase @error('palette.'.$key) border-red-400/50 @enderror"
                           pattern="^#[0-9a-fA-F]{3,6}$"
                           maxlength="7">
                </div>
                @error('palette.'.$key)
                    <p class="text-xs text-red-400 px-3">{{ $message }}</p>
                @enderror
            @endforeach
        </div>
    </div>
</div>
