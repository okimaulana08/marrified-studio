<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-cyan-500/15 border border-cyan-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3zm12-3c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Analytics</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Insight kehadiran tamu, RSVP, dan engagement undangan.</p>
        </div>
    </div>

    {{-- Big-number cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="glass-sm rounded-xl px-3 py-3">
            <p class="text-[10px] uppercase tracking-widest text-white/40 font-semibold">Total Opens</p>
            <p class="text-2xl font-display font-bold text-white mt-1 leading-none">{{ number_format($totalOpens) }}</p>
            <p class="text-[10px] text-white/35 mt-1">dari {{ $totalGuests }} tamu</p>
        </div>
        <div class="glass-sm rounded-xl px-3 py-3">
            <p class="text-[10px] uppercase tracking-widest text-white/40 font-semibold">Sudah Buka</p>
            <p class="text-2xl font-display font-bold text-emerald-300 mt-1 leading-none">{{ $uniqueOpened }}<span class="text-base text-white/30">/{{ $totalGuests }}</span></p>
            <p class="text-[10px] text-emerald-400/80 mt-1">{{ $percentOpened }}%</p>
        </div>
        <div class="glass-sm rounded-xl px-3 py-3">
            <p class="text-[10px] uppercase tracking-widest text-white/40 font-semibold">Konfirmasi Hadir</p>
            <p class="text-2xl font-display font-bold text-white mt-1 leading-none">{{ $attendingCount }}</p>
            <p class="text-[10px] text-white/35 mt-1">~{{ $estimatedHeadcount }} headcount</p>
        </div>
        <div class="glass-sm rounded-xl px-3 py-3">
            <p class="text-[10px] uppercase tracking-widest text-white/40 font-semibold">Ucapan</p>
            <p class="text-2xl font-display font-bold text-white mt-1 leading-none">{{ $guestbookCount }}</p>
            <p class="text-[10px] text-white/35 mt-1">di guestbook</p>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <div class="glass-sm rounded-xl p-4"
             x-data="analyticsDonut(@js([
                'labels' => ['Hadir', 'Tidak hadir', 'Mungkin'],
                'values' => [$attendingCount, $notAttendingCount, $maybeCount],
                'colors' => ['#10b981', '#fb7185', '#f59e0b'],
             ]))"
             wire:ignore>
            <p class="text-[10px] uppercase tracking-widest text-white/40 font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-3 bg-emerald-400/60 rounded-full"></span>
                RSVP Breakdown
            </p>
            @if ($totalRsvp === 0)
                <div class="h-[220px] flex items-center justify-center text-[11px] text-white/35">
                    Belum ada RSVP masuk.
                </div>
            @else
                <div x-ref="host"></div>
            @endif
        </div>

        <div class="glass-sm rounded-xl p-4"
             x-data="analyticsBar(@js([
                'categories' => $histogramCategories,
                'values' => $histogramValues,
                'color' => '#22d3ee',
             ]))"
             wire:ignore>
            <p class="text-[10px] uppercase tracking-widest text-white/40 font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-3 bg-emerald-400/60 rounded-full"></span>
                Opens 14 Hari Terakhir
            </p>
            @if (collect($histogramValues)->sum() === 0)
                <div class="h-[220px] flex items-center justify-center text-[11px] text-white/35">
                    Belum ada tamu yang buka undangan.
                </div>
            @else
                <div x-ref="host"></div>
            @endif
        </div>
    </div>

    {{-- Top openers --}}
    @if ($topOpeners->isNotEmpty())
        <div class="glass-sm rounded-xl p-4">
            <p class="text-[10px] uppercase tracking-widest text-white/40 font-semibold mb-2 flex items-center gap-2">
                <span class="w-1 h-3 bg-emerald-400/60 rounded-full"></span>
                Tamu Paling Aktif
            </p>
            <div class="space-y-1.5">
                @foreach ($topOpeners as $g)
                    <div class="flex items-center justify-between px-2 py-1.5 rounded-lg bg-white/[0.02]">
                        <span class="text-xs text-white/85">{{ $g->name }}</span>
                        <div class="flex items-center gap-3 text-[11px]">
                            <span class="text-white/40">{{ $g->last_opened_at?->diffForHumans() ?? '—' }}</span>
                            <span class="font-mono text-emerald-300/85">{{ $g->opens_count }} ×</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Belum buka — list with WA reminder buttons --}}
    <div class="glass-sm rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-white/8 flex items-center justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-white/40 font-semibold">Belum Buka Undangan</p>
                <p class="text-[11px] text-white/60 mt-0.5">{{ $notOpenedYet->count() }} tamu — kirim reminder via WA</p>
            </div>
        </div>
        @if ($notOpenedYet->isEmpty())
            <div class="px-4 py-6 text-center text-[11px] text-emerald-300/75">
                Semua tamu sudah membuka undangan ✓
            </div>
        @else
            <div class="max-h-[300px] overflow-y-auto divide-y divide-white/5">
                @foreach ($notOpenedYet as $g)
                    @php $wa = $reminderActions[$g->id]['wa_link'] ?? null; @endphp
                    <div class="flex items-center justify-between px-4 py-2">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-white/85 truncate">{{ $g->name }}</p>
                            <p class="text-[10px] text-white/40 font-mono">{{ $g->phone ?: '— tanpa phone —' }}</p>
                        </div>
                        @if ($wa)
                            <a href="{{ $wa }}" target="_blank" rel="noopener"
                               class="btn-ghost text-[11px] px-2 py-0.5 inline-flex items-center gap-1 text-emerald-300/80 hover:text-emerald-300">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                                Reminder
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@once
<script>
    // Alpine factories for the two chart embeds. Lazy-imports the wrapper
    // module so apexcharts is only fetched when this tab is opened.
    window.analyticsDonut = function (config) {
        return {
            chart: null,
            async init() {
                if (typeof window.loadCharts !== 'function') return;
                const host = this.$refs.host;
                if (!host) return; // empty-state placeholder, nothing to render
                const mod = await window.loadCharts();
                this.chart = mod.renderDonut(host, config);
            },
            destroy() { this.chart?.destroy?.(); },
        };
    };
    window.analyticsBar = function (config) {
        return {
            chart: null,
            async init() {
                if (typeof window.loadCharts !== 'function') return;
                const host = this.$refs.host;
                if (!host) return;
                const mod = await window.loadCharts();
                this.chart = mod.renderBar(host, config);
            },
            destroy() { this.chart?.destroy?.(); },
        };
    };
</script>
@endonce
