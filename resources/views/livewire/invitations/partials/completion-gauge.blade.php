@php
    /** Ring gauge inline SVG — no chart lib needed.
     * Stroke-dasharray = circumference; offset shrinks based on percentage. */
    $size = 56;
    $stroke = 5;
    $r = ($size / 2) - $stroke;
    $c = 2 * M_PI * $r;
    $percent = max(0, min(100, (int) $completionPercent));
    $offset = $c * (1 - $percent / 100);
    $tone = $percent >= 100 ? 'emerald' : ($percent >= 70 ? 'lime' : ($percent >= 40 ? 'amber' : 'rose'));
    $colorMap = [
        'emerald' => '#10b981',
        'lime' => '#a3e635',
        'amber' => '#f59e0b',
        'rose' => '#fb7185',
    ];
    $color = $colorMap[$tone];
@endphp

<div class="relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
    <button type="button" x-on:click="open = !open"
            x-on:click.outside="open = false"
            class="flex items-center gap-2 group">
        {{-- Ring with centered % — relative wrapper limited to the SVG box only. --}}
        <div class="relative flex-shrink-0" style="width: {{ $size }}px; height: {{ $size }}px;">
            <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="-rotate-90 transition-transform group-hover:scale-105">
                <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}"
                        stroke="rgba(255,255,255,0.08)" stroke-width="{{ $stroke }}" fill="none"/>
                <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}"
                        stroke="{{ $color }}" stroke-width="{{ $stroke }}" fill="none"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $c }}"
                        stroke-dashoffset="{{ $offset }}"
                        style="transition: stroke-dashoffset 0.6s cubic-bezier(0.16, 1, 0.3, 1);"/>
            </svg>
            <span class="absolute inset-0 flex items-center justify-center text-[11px] font-bold"
                  style="color: {{ $color }};">{{ $percent }}%</span>
        </div>
        <div class="hidden md:flex flex-col items-start text-left">
            <span class="text-[10px] uppercase tracking-widest text-white/40 font-semibold leading-tight">Kelengkapan</span>
            <span class="text-xs font-medium leading-tight mt-0.5" style="color: {{ $color }};">
                @if (count($completionTodos) === 0)
                    Semua siap ✓
                @else
                    {{ count($completionTodos) }} item belum
                @endif
            </span>
        </div>
    </button>

    {{-- Dropdown: list of pending todos --}}
    <div x-show="open" x-cloak x-transition
         class="absolute left-0 top-full mt-2 w-80 z-30 glass-strong rounded-xl border border-white/10 shadow-2xl overflow-hidden"
         style="background: rgba(15, 17, 23, 0.95);">
        <div class="px-4 py-3 border-b border-white/8">
            <p class="text-xs font-semibold text-white">Checklist Kelengkapan</p>
            <p class="text-[11px] text-white/45 mt-0.5">{{ $percent }}% selesai · klik item untuk lompat ke tab</p>
        </div>
        <div class="max-h-72 overflow-y-auto py-1.5">
            @forelse ($completionTodos as $todo)
                <button type="button"
                        x-on:click="$dispatch('jump-to-tab', '{{ $todo['tab'] }}'); open = false"
                        class="w-full flex items-center gap-2.5 px-4 py-2 text-left hover:bg-emerald-500/10 transition-colors text-[12px] text-white/75 hover:text-white">
                    <svg class="w-3.5 h-3.5 text-amber-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/>
                    </svg>
                    <span class="flex-1">{{ $todo['label'] }}</span>
                    <svg class="w-3 h-3 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @empty
                <div class="px-4 py-6 text-center">
                    <svg class="w-10 h-10 mx-auto text-emerald-400/80 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-white/65">Undangan kamu sudah lengkap.</p>
                    <p class="text-[11px] text-white/40 mt-0.5">Siap dibagikan ke tamu.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
