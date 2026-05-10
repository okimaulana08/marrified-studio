<div>
    {{-- Flash --}}
    @if ($flashMessage)
        <div wire:key="flash-{{ md5($flashMessage) }}"
             x-data="{ show: true }" x-show="show" x-cloak x-transition
             x-init="setTimeout(() => show = false, 4000)"
             class="mb-4 px-4 py-3 rounded-xl text-sm font-medium glass-strong
                    {{ $flashType === 'error' ? 'border-red-400/30 text-red-300' : 'border-emerald-400/30 text-emerald-300' }}">
            {{ $flashMessage }}
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="glass rounded-2xl px-4 py-3 mb-4 flex items-center gap-3">
        <div class="relative flex-1">
            <svg class="absolute top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 pointer-events-none"
                 style="left: 14px;"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Cari slug atau nama mempelai..."
                   style="padding-left: 42px;"
                   class="admin-input w-full pr-3 py-2 text-sm">
        </div>
        <a href="{{ route('admin.invitations.create') }}" class="btn-primary text-xs flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Invitation
        </a>
    </div>

    {{-- Table --}}
    @if ($rows->isEmpty())
        <div class="glass rounded-2xl p-12 text-center">
            @if ($search !== '')
                <div class="w-12 h-12 mx-auto rounded-2xl bg-white/5 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <p class="text-white/60 text-sm">
                    Tidak ada invitation cocok dengan "<span class="font-mono text-emerald-300">{{ $search }}</span>".
                </p>
                <button wire:click="$set('search', '')" class="btn-ghost text-xs mt-4">Reset pencarian</button>
            @else
                <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center mb-4"
                     style="background: linear-gradient(135deg, rgba(232,62,140,0.18), rgba(255,122,133,0.12)); border: 1px solid rgba(232,62,140,0.25);">
                    <svg class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-semibold text-white mb-1">Belum ada invitation</h3>
                <p class="text-sm text-white/50 mb-5 max-w-sm mx-auto">
                    Buat invitation pertama: pilih tema, set slug, lalu lengkapi data couple dan tamu.
                </p>
                <a href="{{ route('admin.invitations.create') }}" class="btn-primary text-xs inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Invitation Pertama
                </a>
            @endif
        </div>
    @else
        <div class="glass rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-white/5 border-b border-white/8">
                    <tr class="text-left text-[11px] uppercase tracking-widest text-white/50">
                        <th class="px-4 py-2.5 font-semibold">Slug</th>
                        <th class="px-4 py-2.5 font-semibold">Couple</th>
                        <th class="px-4 py-2.5 font-semibold">Tema</th>
                        <th class="px-4 py-2.5 font-semibold">Couple Login</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Tamu</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $inv)
                        <tr wire:key="row-{{ $inv->id }}" class="border-t border-white/5 hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 font-mono text-emerald-300/90 text-xs">{{ $inv->slug }}</td>
                            <td class="px-4 py-3 text-white/85">
                                @if ($inv->couple)
                                    {{ $inv->couple->bride_name }} &amp; {{ $inv->couple->groom_name }}
                                @else
                                    <span class="text-white/30 italic">— belum diisi —</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-white/60 font-mono text-xs">{{ $inv->theme_slug }}</td>
                            <td class="px-4 py-3 text-white/60 text-xs">
                                @if ($inv->user)
                                    <span class="text-emerald-400">●</span> {{ $inv->user->email }}
                                @else
                                    <span class="text-amber-300/70">— belum di-issue —</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-white/60 font-mono text-xs">{{ $inv->guests_count }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('invitations.edit', $inv->slug) }}" class="btn-ghost text-xs">Edit</a>
                                    <a href="{{ route('admin.invitations.credentials', $inv->slug) }}"
                                       class="btn-ghost text-xs {{ $inv->user_id === null ? 'text-amber-300/80 hover:text-amber-300' : '' }}"
                                       title="{{ $inv->user_id === null ? 'Generate kredensial couple' : 'Manage kredensial' }}">
                                        {{ $inv->user_id === null ? 'Issue Login' : 'Login' }}
                                    </a>
                                    <button type="button" wire:click="openCloneModal({{ $inv->id }})"
                                            class="btn-ghost text-xs"
                                            title="Duplikasi invitation ini ke slug baru">
                                        Duplikat
                                    </button>
                                    <button type="button" wire:click="confirmDelete({{ $inv->id }})"
                                            class="btn-ghost text-xs text-red-300/80 hover:text-red-300">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Clone modal --}}
    @if ($showCloneModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-data x-on:keydown.escape.window="$wire.closeCloneModal()">
            <div class="glass rounded-2xl p-6 max-w-md w-full">
                <h3 class="font-display text-xl font-semibold text-white mb-2">Duplikat Invitation</h3>
                <p class="text-sm text-white/60 mb-1">
                    Sumber: <span class="font-mono text-emerald-300">{{ $cloneSourceSlug }}</span>
                </p>
                <p class="text-[11px] text-white/40 mb-4 leading-relaxed">
                    Ini akan menyalin couple, events, sections, gift accounts, foto, dan daftar tamu
                    (token guest <strong>akan di-regenerate</strong>). Login couple TIDAK ikut tersalin —
                    admin perlu issue ulang kredensial untuk slug baru.
                </p>

                <div>
                    <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">
                        Slug Baru *
                    </label>
                    <input wire:model="cloneTargetSlug" type="text"
                           class="admin-input w-full px-3 py-2 text-sm font-mono @error('cloneTargetSlug') border-red-400/50 @enderror">
                    @error('cloneTargetSlug') <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2 mt-5">
                    <button wire:click="closeCloneModal" class="btn-ghost text-xs">Batal</button>
                    <button wire:click="confirmClone" wire:loading.attr="disabled" wire:target="confirmClone"
                            class="btn-primary text-xs">
                        <span wire:loading.remove wire:target="confirmClone">Duplikat &amp; Buka Editor</span>
                        <span wire:loading wire:target="confirmClone">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-data x-on:keydown.escape.window="$wire.closeDeleteModal()">
            <div class="glass rounded-2xl p-6 max-w-md w-full">
                <h3 class="font-display text-xl font-semibold text-white mb-2">Hapus Invitation?</h3>
                <p class="text-sm text-white/60 mb-1">
                    Slug: <span class="font-mono text-emerald-300">{{ $deleteTargetSlug }}</span>
                </p>
                <p class="text-sm text-red-300/70 mb-5">
                    Semua data couple, events, sections, gift accounts, guest tokens, dan media akan ikut terhapus permanen.
                </p>
                <div class="flex justify-end gap-2">
                    <button wire:click="closeDeleteModal" class="btn-ghost text-xs">Batal</button>
                    <button wire:click="deleteInvitation" wire:loading.attr="disabled"
                            class="btn-primary text-xs"
                            style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        Ya, hapus
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
