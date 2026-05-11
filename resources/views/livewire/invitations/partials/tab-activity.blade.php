@php
    $logLabels = [
        'invitation' => 'Undangan',
        'section' => 'Section',
        'couple' => 'Pengantin',
        'event' => 'Acara',
        'guest' => 'Tamu',
        'gift' => 'Hadiah',
        'guestbook' => 'Buku Tamu',
    ];
    $logIcons = [
        'invitation' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'section' => 'M4 6h16M4 12h16M4 18h7',
        'couple' => 'M4.318 6.318a4.5 4.5 0 010 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        'event' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'guest' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'gift' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
        'guestbook' => 'M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z',
    ];
    $eventColors = [
        'created' => 'bg-emerald-500/15 text-emerald-300 border-emerald-400/25',
        'updated' => 'bg-blue-500/15 text-blue-300 border-blue-400/25',
        'deleted' => 'bg-rose-500/15 text-rose-300 border-rose-400/25',
    ];
    $eventLabels = [
        'created' => 'Dibuat',
        'updated' => 'Diubah',
        'deleted' => 'Dihapus',
    ];
@endphp

<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-indigo-500/15 border border-indigo-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Riwayat Aktivitas</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Audit trail siapa-mengubah-apa-kapan. Admin-only.</p>
        </div>
    </div>

    {{-- Filter row --}}
    <div class="flex flex-wrap items-center gap-2">
        <select wire:model.live="logFilter" class="admin-select px-2.5 py-1.5 text-xs">
            <option value="">Semua kategori</option>
            @foreach ($logNames as $log)
                <option value="{{ $log }}">{{ $logLabels[$log] ?? ucfirst($log) }}</option>
            @endforeach
        </select>

        @if ($causers->isNotEmpty())
            <select wire:model.live="userFilter" class="admin-select px-2.5 py-1.5 text-xs">
                <option value="">Semua user</option>
                @foreach ($causers as $u)
                    <option value="{{ $u->id }}">{{ $u->name ?? $u->email }}</option>
                @endforeach
            </select>
        @endif

        <span class="text-xs text-white/40 ml-auto">{{ $activities->total() }} log entri</span>
    </div>

    {{-- Activity stream --}}
    @if ($activities->isEmpty())
        <div class="glass-sm rounded-xl p-12 text-center text-white/40 text-sm">
            Belum ada aktivitas yang ter-log untuk undangan ini.
        </div>
    @else
        <div class="space-y-2">
            @foreach ($activities as $a)
                @php
                    $log = (string) $a->log_name;
                    $event = (string) $a->event;
                    $iconD = $logIcons[$log] ?? 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2';
                    $changes = $a->changes ?? collect();
                    $oldAttrs = (array) ($changes['old'] ?? []);
                    $newAttrs = (array) ($changes['attributes'] ?? []);
                @endphp
                <article wire:key="activity-{{ $a->id }}"
                         class="glass-sm rounded-xl px-3 py-2.5 flex items-start gap-3">
                    <div class="w-7 h-7 rounded-lg bg-white/5 border border-white/8 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconD }}"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap text-xs">
                            <span class="text-white/80 font-semibold">{{ $logLabels[$log] ?? ucfirst($log) }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] border {{ $eventColors[$event] ?? 'bg-white/5 text-white/55 border-white/15' }}">
                                {{ $eventLabels[$event] ?? $event }}
                            </span>
                            @if ($a->description)
                                <span class="text-white/50">·</span>
                                <span class="text-white/55 truncate">{{ $a->description }}</span>
                            @endif
                        </div>

                        @if (! empty($newAttrs))
                            <div class="mt-1.5 space-y-0.5">
                                @foreach ($newAttrs as $field => $newVal)
                                    @php $oldVal = $oldAttrs[$field] ?? null; @endphp
                                    <div class="text-[11px] flex items-baseline gap-1.5 flex-wrap">
                                        <code class="text-white/45 font-mono">{{ $field }}:</code>
                                        @if ($event === 'updated' && $oldVal !== null)
                                            <code class="text-rose-300/75 font-mono line-through">{{ is_array($oldVal) ? json_encode($oldVal) : (string) $oldVal }}</code>
                                            <span class="text-white/30">→</span>
                                        @endif
                                        <code class="text-emerald-300/80 font-mono">{{ is_array($newVal) ? json_encode($newVal) : (string) $newVal }}</code>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="text-[10px] text-white/35 mt-1.5 flex items-center gap-2">
                            <span>{{ $a->created_at?->translatedFormat('d M Y, H:i') }}</span>
                            @if ($a->causer)
                                <span>·</span>
                                <span>oleh {{ $a->causer->name ?? $a->causer->email }}</span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="text-[10px] text-white/40 mt-2">{{ $activities->links() }}</div>
    @endif
</div>
