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
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Cari slug atau nama mempelai..."
                   class="admin-input w-full pl-10 pr-3 py-2 text-sm">
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
        <div class="glass rounded-2xl p-16 text-center">
            <p class="text-white/60 text-sm">
                @if ($search !== '')
                    Tidak ada invitation cocok dengan "<span class="font-mono text-emerald-300">{{ $search }}</span>".
                @else
                    Belum ada invitation. Klik <strong>New Invitation</strong> untuk membuat yang pertama.
                @endif
            </p>
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
                                <a href="{{ route('invitations.edit', $inv->slug) }}" class="btn-ghost text-xs">Edit</a>
                                <a href="{{ route('admin.invitations.credentials', $inv->slug) }}"
                                   class="btn-ghost text-xs {{ $inv->user_id === null ? 'text-amber-300/80 hover:text-amber-300' : '' }}"
                                   title="{{ $inv->user_id === null ? 'Generate kredensial couple' : 'Manage kredensial' }}">
                                    {{ $inv->user_id === null ? 'Issue Login' : 'Login' }}
                                </a>
                                <button type="button" wire:click="confirmDelete({{ $inv->id }})"
                                        class="btn-ghost text-xs text-red-300/80 hover:text-red-300">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
