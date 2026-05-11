<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Moderasi Buku Tamu</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Sembunyikan ucapan tidak pantas / spam dari render publik. Data tetap tersimpan.</p>
        </div>
    </div>

    {{-- Flash --}}
    @if ($flashMessage)
        <div wire:key="moderation-flash-{{ md5($flashMessage) }}"
             x-data="{ show: true }" x-show="show" x-cloak x-transition
             x-init="setTimeout(() => show = false, 3500)"
             class="px-3 py-2 rounded-lg text-xs font-medium glass-strong
                    {{ $flashType === 'error' ? 'border-red-400/30 text-red-300' : 'border-emerald-400/30 text-emerald-300' }}">
            {{ $flashMessage }}
        </div>
    @endif

    {{-- Filter chips + search --}}
    <div class="flex flex-wrap items-center gap-2">
        @foreach (['all' => 'Semua', 'visible' => 'Tampil', 'hidden' => 'Disembunyikan'] as $k => $label)
            <button type="button" wire:click="$set('filter', '{{ $k }}')"
                    @class([
                        'px-2.5 py-1 text-xs font-semibold rounded-lg border transition-all',
                        'bg-emerald-500/20 text-emerald-100 border-emerald-400/40' => $filter === $k,
                        'glass-sm text-white/55 hover:text-white border-transparent' => $filter !== $k,
                    ])>
                {{ $label }}
                <span class="ml-1 text-[10px] font-mono opacity-65">{{ $counts[$k] }}</span>
            </button>
        @endforeach
        <div class="flex-1 relative">
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Cari nama atau isi ucapan…"
                   class="admin-input w-full pl-7 pr-3 py-1.5 text-xs">
            <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3 h-3 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    {{-- List --}}
    @if ($messages->isEmpty())
        <div class="glass-sm rounded-xl p-10 text-center text-white/40 text-sm">
            @if ($search !== '')
                Tidak ada ucapan cocok dengan "<span class="font-mono text-emerald-300">{{ $search }}</span>".
            @elseif ($filter === 'hidden')
                Tidak ada ucapan yang disembunyikan.
            @else
                Belum ada ucapan masuk.
            @endif
        </div>
    @else
        <div class="space-y-2">
            @foreach ($messages as $m)
                <article wire:key="msg-{{ $m->id }}"
                         class="glass-sm rounded-xl px-3 py-3 flex items-start gap-3 transition-all
                                {{ $m->is_visible ? '' : 'opacity-60' }}">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-sm font-semibold text-white/85 truncate">{{ $m->name }}</p>
                            <span class="text-[10px] text-white/35">·</span>
                            <p class="text-[10px] text-white/40">{{ $m->created_at?->diffForHumans() }}</p>
                            @unless ($m->is_visible)
                                <span class="px-1.5 py-px text-[9px] uppercase tracking-wider font-mono rounded bg-amber-400/15 text-amber-300/85">disembunyikan</span>
                            @endunless
                        </div>
                        <p class="text-xs text-white/70 leading-relaxed whitespace-pre-wrap">{{ $m->message }}</p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button wire:click="toggleVisibility({{ $m->id }})"
                                title="{{ $m->is_visible ? 'Sembunyikan' : 'Tampilkan' }}"
                                class="p-1.5 rounded text-white/45 hover:text-white hover:bg-white/[0.05] transition-colors">
                            @if ($m->is_visible)
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            @else
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            @endif
                        </button>
                        <button wire:click="confirmDelete({{ $m->id }})"
                                title="Hapus permanen"
                                class="p-1.5 rounded text-white/45 hover:text-red-300 hover:bg-red-500/10 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                            </svg>
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="text-[10px] text-white/40 mt-2">{{ $messages->links() }}</div>
    @endif

    {{-- Delete confirm modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-data x-on:keydown.escape.window="$wire.closeDeleteModal()">
            <div class="glass rounded-2xl p-6 max-w-md w-full">
                <h3 class="font-display text-lg font-semibold text-white mb-2">Hapus permanen?</h3>
                <p class="text-sm text-white/65 mb-4">
                    Ucapan dari <span class="text-white font-semibold">{{ $deleteTargetName }}</span> akan dihapus dan tidak bisa dipulihkan. Kalau hanya ingin sembunyikan dari publik, gunakan tombol mata.
                </p>
                <div class="flex justify-end gap-2">
                    <button wire:click="closeDeleteModal" class="btn-ghost text-xs">Batal</button>
                    <button wire:click="deleteMessage"
                            class="btn-primary text-xs"
                            style="background: linear-gradient(135deg, #ef4444, #b91c1c);">
                        Hapus Permanen
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
