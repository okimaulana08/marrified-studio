<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-purple-500/15 border border-purple-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Daftar Tamu</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Tambah manual atau import CSV — token otomatis dibuat per tamu.</p>
        </div>
    </div>

    {{-- Flash --}}
    @if ($flashMessage)
        <div wire:key="guests-flash-{{ md5($flashMessage) }}"
             x-data="{ show: true }" x-show="show" x-cloak x-transition
             x-init="setTimeout(() => show = false, 4000)"
             class="px-3 py-2 rounded-lg text-xs font-medium glass-strong
                    {{ $flashType === 'error' ? 'border-red-400/30 text-red-300' : 'border-emerald-400/30 text-emerald-300' }}">
            {{ $flashMessage }}
        </div>
    @endif

    {{-- WhatsApp template editor (collapsible) --}}
    <div class="glass-sm rounded-xl overflow-hidden"
         x-data="{ open: @entangle('showTemplateEditor') }">
        <button type="button" x-on:click="open = !open"
                class="w-full px-4 py-2.5 flex items-center justify-between hover:bg-white/[0.02] transition-colors">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-emerald-300" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/>
                </svg>
                <div class="text-left">
                    <p class="text-xs font-semibold text-white/85">Template Pesan WhatsApp</p>
                    <p class="text-[10px] text-white/40">Pesan yang dipakai untuk semua tamu — placeholder otomatis di-replace per orang</p>
                </div>
            </div>
            <svg class="w-3.5 h-3.5 text-white/40 transition-transform" :class="open ? 'rotate-180' : ''"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" x-collapse class="px-4 pb-4 space-y-2 border-t border-white/8 pt-3">
            <textarea wire:model="waTemplate" rows="8"
                      class="admin-input w-full px-3 py-2 text-xs font-mono whitespace-pre"></textarea>
            <div class="flex flex-wrap items-center gap-1.5 text-[10px]">
                <span class="text-white/40">Placeholder:</span>
                @foreach (['{nama}', '{bride}', '{groom}', '{tanggal}', '{link}'] as $ph)
                    <code class="px-1.5 py-0.5 rounded bg-emerald-500/10 border border-emerald-400/20 text-emerald-300 font-mono">{{ $ph }}</code>
                @endforeach
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button wire:click="resetWaTemplate" wire:confirm="Kembalikan ke template default?"
                        class="btn-ghost text-xs">Reset Default</button>
                <button wire:click="saveWaTemplate" wire:loading.attr="disabled" wire:target="saveWaTemplate"
                        class="btn-primary text-xs">
                    <span wire:loading.remove wire:target="saveWaTemplate">Simpan Template</span>
                    <span wire:loading wire:target="saveWaTemplate">Menyimpan…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Inline add-form --}}
    <div class="glass-sm rounded-xl p-4 space-y-3">
        <p class="text-[10px] uppercase tracking-widest text-emerald-400/70 font-bold">Tambah Tamu</p>
        <div class="grid grid-cols-12 gap-2">
            <div class="col-span-4">
                <input wire:model="form.name" type="text" placeholder="Nama lengkap *"
                       class="admin-input w-full px-2 py-1.5 text-xs @error('form.name') border-red-400/50 @enderror">
            </div>
            <div class="col-span-2">
                <input wire:model="form.relation" type="text" placeholder="Relasi (Bapak/Ibu/...)"
                       class="admin-input w-full px-2 py-1.5 text-xs">
            </div>
            <div class="col-span-2">
                <select wire:model="form.group" class="admin-select w-full px-2 py-1.5 text-xs">
                    <option value="">— Grup —</option>
                    @foreach (\App\Enums\GuestGroup::options() as $opt)
                        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-3">
                <input wire:model="form.phone" type="tel" placeholder="Phone (opsional)"
                       class="admin-input w-full px-2 py-1.5 text-xs font-mono">
            </div>
            <div class="col-span-1">
                <button wire:click="addGuest" wire:loading.attr="disabled" wire:target="addGuest"
                        class="btn-primary w-full text-xs py-1.5">+</button>
            </div>
        </div>
        @error('form.name') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
    </div>

    {{-- Group filter chips --}}
    <div class="flex flex-wrap items-center gap-1.5">
        <button type="button" wire:click="$set('groupFilter', '')"
                @class([
                    'px-2.5 py-1 text-[11px] font-semibold rounded-lg border transition-all',
                    'bg-emerald-500/20 text-emerald-100 border-emerald-400/40' => $groupFilter === '',
                    'glass-sm text-white/55 hover:text-white border-transparent' => $groupFilter !== '',
                ])>
            Semua <span class="ml-1 font-mono text-[10px] opacity-65">{{ $groupCounts['all'] ?? 0 }}</span>
        </button>
        @foreach (\App\Enums\GuestGroup::options() as $opt)
            @php $cnt = $groupCounts[$opt['value']] ?? 0; @endphp
            <button type="button" wire:click="$set('groupFilter', '{{ $opt['value'] }}')"
                    @class([
                        'px-2.5 py-1 text-[11px] font-semibold rounded-lg border transition-all',
                        'bg-emerald-500/20 text-emerald-100 border-emerald-400/40' => $groupFilter === $opt['value'],
                        'glass-sm text-white/55 hover:text-white border-transparent' => $groupFilter !== $opt['value'],
                    ])>
                {{ $opt['label'] }} <span class="ml-1 font-mono text-[10px] opacity-65">{{ $cnt }}</span>
            </button>
        @endforeach
        @if (($groupCounts['none'] ?? 0) > 0)
            <button type="button" wire:click="$set('groupFilter', 'none')"
                    @class([
                        'px-2.5 py-1 text-[11px] font-semibold rounded-lg border transition-all italic',
                        'bg-emerald-500/20 text-emerald-100 border-emerald-400/40' => $groupFilter === 'none',
                        'glass-sm text-white/55 hover:text-white border-transparent' => $groupFilter !== 'none',
                    ])>
                Tanpa grup <span class="ml-1 font-mono text-[10px] opacity-65 not-italic">{{ $groupCounts['none'] }}</span>
            </button>
        @endif
    </div>

    {{-- Toolbar: search + CSV --}}
    <div class="flex items-center gap-3">
        <div class="relative flex-1">
            <svg class="absolute top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-white/30 pointer-events-none"
                 style="left: 12px;"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Cari nama / relasi / phone..."
                   style="padding-left: 36px;"
                   class="admin-input w-full pr-3 py-1.5 text-xs">
        </div>
        <button wire:click="openCsvModal" class="btn-ghost text-xs flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            Import CSV
        </button>
        <button wire:click="exportCsv" class="btn-ghost text-xs flex items-center gap-1.5"
                wire:loading.attr="disabled" wire:target="exportCsv">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export CSV
        </button>
    </div>

    {{-- Table --}}
    @if ($guests->isEmpty())
        <div class="glass-sm rounded-xl p-12 text-center text-white/40 text-sm">
            @if ($search !== '')
                Tidak ada tamu cocok dengan "<span class="font-mono text-emerald-300">{{ $search }}</span>".
            @else
                Belum ada tamu. Tambah manual di atas atau import CSV.
            @endif
        </div>
    @else
        <div class="glass-sm rounded-xl overflow-x-auto">
            <table class="w-full text-xs min-w-[820px]">
                <thead class="bg-white/5 border-b border-white/8">
                    <tr class="text-left text-[10px] uppercase tracking-widest text-white/50">
                        <th class="px-3 py-2 font-semibold">Nama</th>
                        <th class="px-3 py-2 font-semibold">Relasi</th>
                        <th class="px-3 py-2 font-semibold">Grup</th>
                        <th class="px-3 py-2 font-semibold">Phone</th>
                        <th class="px-3 py-2 font-semibold">Token URL</th>
                        <th class="px-3 py-2 font-semibold text-right">Open</th>
                        <th class="px-3 py-2 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($guests as $g)
                        @php
                            $url = url("/{$invitationSlug}/{$g->token}");
                            $waLink = $guestActions[$g->id]['wa_link'] ?? null;
                            $waMessage = $guestActions[$g->id]['message'] ?? '';
                        @endphp
                        <tr wire:key="guest-{{ $g->id }}" class="border-t border-white/5 hover:bg-white/[0.02]">
                            <td class="px-3 py-2 text-white/85">{{ $g->name }}</td>
                            <td class="px-3 py-2 text-white/60">{{ $g->relation ?: '—' }}</td>
                            <td class="px-3 py-2 text-white/60">
                                @if ($g->group)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/10 border border-emerald-400/20 text-emerald-300">{{ \App\Enums\GuestGroup::labelFor($g->group) }}</span>
                                @else
                                    <span class="text-white/30 italic">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-white/60 font-mono">{{ $g->phone ?: '—' }}</td>
                            <td class="px-3 py-2"
                                x-data="{ copied: false }"
                                x-init="$el.querySelector('button').addEventListener('click', () => {
                                    navigator.clipboard.writeText('{{ $url }}');
                                    copied = true;
                                    setTimeout(() => copied = false, 1500);
                                })">
                                <button type="button"
                                        class="font-mono text-[10px] text-emerald-300/70 hover:text-emerald-300 truncate max-w-[200px] inline-flex items-center gap-1">
                                    <span x-show="!copied">{{ $g->token }} ↗</span>
                                    <span x-show="copied" x-cloak class="text-emerald-300">✓ tersalin</span>
                                </button>
                            </td>
                            <td class="px-3 py-2 text-right text-white/60 font-mono">{{ $g->opens_count }}</td>
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-0.5">
                                    @php $qrUrl = route('invitations.guests.qr', ['slug' => $invitationSlug, 'guest' => $g->id]); @endphp
                                    @if ($waLink)
                                        <a href="{{ $waLink }}" target="_blank" rel="noopener"
                                           title="Kirim via WhatsApp"
                                           class="row-action-btn text-emerald-300/80 hover:text-emerald-300 hover:bg-emerald-500/10">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    <button type="button"
                                            x-data
                                            x-on:click="$dispatch('show-qr', { name: @js($g->name), url: @js($qrUrl) })"
                                            title="Tampilkan QR per-tamu"
                                            class="row-action-btn text-white/55 hover:text-white hover:bg-white/[0.08]">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 6v-3m0 0v-3m0 3h3m3 0v3m-3-3v-3m3 0h3m-3 0v-3"/>
                                        </svg>
                                    </button>
                                    <button type="button"
                                            x-data="{ ok: false }"
                                            x-on:click="navigator.clipboard.writeText(@js($waMessage)); ok = true; setTimeout(() => ok = false, 1500)"
                                            :title="ok ? 'Tersalin!' : 'Salin pesan ke clipboard'"
                                            class="row-action-btn text-white/55 hover:text-white hover:bg-white/[0.08]"
                                            :class="ok && 'text-emerald-300'">
                                        <svg x-show="!ok" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                        </svg>
                                        <svg x-show="ok" x-cloak class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <button wire:click="startEdit({{ $g->id }})"
                                            title="Edit data tamu"
                                            class="row-action-btn text-white/55 hover:text-white hover:bg-white/[0.08]">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $g->id }})"
                                            title="Hapus tamu"
                                            class="row-action-btn text-white/55 hover:text-red-300 hover:bg-red-500/10">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-[10px] text-white/40 mt-2">{{ $guests->links() }}</div>
    @endif

    {{-- Edit modal --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-data x-on:keydown.escape.window="$wire.closeEditModal()">
            <div class="glass rounded-2xl p-6 max-w-md w-full">
                <h3 class="font-display text-xl font-semibold text-white mb-4">Edit Tamu</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] text-white/50 mb-1">Nama *</label>
                        <input wire:model="form.name" type="text"
                               class="admin-input w-full px-3 py-2 text-sm @error('form.name') border-red-400/50 @enderror">
                        @error('form.name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] text-white/50 mb-1">Relasi</label>
                        <input wire:model="form.relation" type="text" class="admin-input w-full px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] text-white/50 mb-1">Grup</label>
                        <select wire:model="form.group" class="admin-select w-full px-3 py-2 text-sm">
                            <option value="">— Tanpa grup —</option>
                            @foreach (\App\Enums\GuestGroup::options() as $opt)
                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] text-white/50 mb-1">Phone</label>
                        <input wire:model="form.phone" type="tel" class="admin-input w-full px-3 py-2 text-sm font-mono">
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button wire:click="closeEditModal" class="btn-ghost text-xs">Batal</button>
                    <button wire:click="saveEdit" wire:loading.attr="disabled" class="btn-primary text-xs">Simpan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-data x-on:keydown.escape.window="$wire.closeDeleteModal()">
            <div class="glass rounded-2xl p-6 max-w-md w-full">
                <h3 class="font-display text-xl font-semibold text-white mb-2">Hapus Tamu?</h3>
                <p class="text-sm text-white/60 mb-5">
                    <strong>{{ $deleteTargetName }}</strong> akan dihapus permanen, beserta token URL miliknya.
                </p>
                <div class="flex justify-end gap-2">
                    <button wire:click="closeDeleteModal" class="btn-ghost text-xs">Batal</button>
                    <button wire:click="deleteGuest" class="btn-primary text-xs"
                            style="background: linear-gradient(135deg, #ef4444, #dc2626);">Ya, hapus</button>
                </div>
            </div>
        </div>
    @endif

    {{-- CSV import modal --}}
    @if ($showCsvModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-data x-on:keydown.escape.window="$wire.closeCsvModal()">
            <div class="glass rounded-2xl p-6 max-w-3xl w-full max-h-[80vh] overflow-y-auto">
                <h3 class="font-display text-xl font-semibold text-white mb-2">Import Daftar Tamu CSV</h3>
                <p class="text-sm text-white/60 mb-4">
                    Format CSV: <code class="font-mono text-emerald-300">name,relation,phone</code> (header wajib).
                    Maksimal {{ \App\Services\Invitations\GuestCsvImporter::MAX_ROWS }} baris.
                </p>

                @if (! $csvParsed)
                    <input type="file" wire:model="csvFile" accept=".csv,.txt"
                           class="text-xs text-white/60 mb-3">
                    <div wire:loading wire:target="csvFile" class="text-[11px] text-white/40 mb-2">Mengunggah...</div>
                    @error('csvFile') <p class="text-xs text-red-400">{{ $message }}</p> @enderror

                    @if ($csvError)
                        <div class="px-3 py-2 mb-3 rounded-lg bg-red-400/10 border border-red-400/20 text-xs text-red-300">
                            {{ $csvError }}
                        </div>
                    @endif

                    <div class="flex justify-end gap-2 mt-4">
                        <button wire:click="closeCsvModal" class="btn-ghost text-xs">Batal</button>
                        <button wire:click="previewCsv" wire:loading.attr="disabled" wire:target="previewCsv,csvFile"
                                @disabled(! $csvFile)
                                class="btn-primary text-xs">
                            <span wire:loading.remove wire:target="previewCsv">Preview</span>
                            <span wire:loading wire:target="previewCsv">Memuat...</span>
                        </button>
                    </div>
                @else
                    @php
                        $valid = collect($csvPreview)->filter(fn ($r) => empty($r['errors']))->count();
                        $invalid = count($csvPreview) - $valid;
                    @endphp
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="glass-sm rounded-xl px-3 py-2">
                            <p class="text-[10px] text-white/40 uppercase tracking-wider">Valid</p>
                            <p class="text-2xl font-display font-semibold text-emerald-400 leading-none mt-1">{{ $valid }}</p>
                        </div>
                        <div class="glass-sm rounded-xl px-3 py-2">
                            <p class="text-[10px] text-white/40 uppercase tracking-wider">Akan di-skip</p>
                            <p class="text-2xl font-display font-semibold text-red-300 leading-none mt-1">{{ $invalid }}</p>
                        </div>
                    </div>

                    <div class="glass-sm rounded-xl overflow-hidden mb-4 max-h-72 overflow-y-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-white/5 border-b border-white/8 sticky top-0">
                                <tr class="text-left text-[10px] uppercase tracking-widest text-white/50">
                                    <th class="px-3 py-2 font-semibold">#</th>
                                    <th class="px-3 py-2 font-semibold">Nama</th>
                                    <th class="px-3 py-2 font-semibold">Relasi</th>
                                    <th class="px-3 py-2 font-semibold">Phone</th>
                                    <th class="px-3 py-2 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($csvPreview as $i => $row)
                                    <tr class="border-t border-white/5 {{ ! empty($row['errors']) ? 'bg-red-400/5' : '' }}">
                                        <td class="px-3 py-1.5 text-white/40 font-mono">{{ $i + 1 }}</td>
                                        <td class="px-3 py-1.5 text-white/85">{{ $row['name'] ?: '—' }}</td>
                                        <td class="px-3 py-1.5 text-white/60">{{ $row['relation'] ?: '—' }}</td>
                                        <td class="px-3 py-1.5 text-white/60 font-mono">{{ $row['phone'] ?: '—' }}</td>
                                        <td class="px-3 py-1.5">
                                            @if (empty($row['errors']))
                                                <span class="text-emerald-400">✓</span>
                                            @else
                                                <span class="text-red-300 text-[10px]">{{ implode(', ', $row['errors']) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button wire:click="closeCsvModal" class="btn-ghost text-xs">Batal</button>
                        <button wire:click="confirmImport" wire:loading.attr="disabled"
                                @disabled($valid === 0)
                                class="btn-primary text-xs">
                            Import {{ $valid }} Tamu
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
