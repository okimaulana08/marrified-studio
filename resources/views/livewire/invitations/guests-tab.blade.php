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

    {{-- Inline add-form --}}
    <div class="glass-sm rounded-xl p-4 space-y-3">
        <p class="text-[10px] uppercase tracking-widest text-emerald-400/70 font-bold">Tambah Tamu</p>
        <div class="grid grid-cols-12 gap-2">
            <div class="col-span-5">
                <input wire:model="form.name" type="text" placeholder="Nama lengkap *"
                       class="admin-input w-full px-2 py-1.5 text-xs @error('form.name') border-red-400/50 @enderror">
            </div>
            <div class="col-span-3">
                <input wire:model="form.relation" type="text" placeholder="Relasi (Bapak/Ibu/...)"
                       class="admin-input w-full px-2 py-1.5 text-xs">
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

    {{-- Toolbar: search + CSV --}}
    <div class="flex items-center gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Cari nama / relasi / phone..."
                   class="admin-input w-full pl-9 pr-3 py-1.5 text-xs">
        </div>
        <button wire:click="openCsvModal" class="btn-ghost text-xs flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            Import CSV
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
        <div class="glass-sm rounded-xl overflow-hidden">
            <table class="w-full text-xs">
                <thead class="bg-white/5 border-b border-white/8">
                    <tr class="text-left text-[10px] uppercase tracking-widest text-white/50">
                        <th class="px-3 py-2 font-semibold">Nama</th>
                        <th class="px-3 py-2 font-semibold">Relasi</th>
                        <th class="px-3 py-2 font-semibold">Phone</th>
                        <th class="px-3 py-2 font-semibold">Token URL</th>
                        <th class="px-3 py-2 font-semibold text-right">Open</th>
                        <th class="px-3 py-2 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($guests as $g)
                        @php $url = url("/{$invitationSlug}/{$g->token}"); @endphp
                        <tr wire:key="guest-{{ $g->id }}" class="border-t border-white/5 hover:bg-white/[0.02]">
                            <td class="px-3 py-2 text-white/85">{{ $g->name }}</td>
                            <td class="px-3 py-2 text-white/60">{{ $g->relation ?: '—' }}</td>
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
                                <button wire:click="startEdit({{ $g->id }})" class="btn-ghost text-xs px-2 py-0.5">Edit</button>
                                <button wire:click="confirmDelete({{ $g->id }})" class="btn-ghost text-xs px-2 py-0.5 text-red-300/80 hover:text-red-300">Hapus</button>
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
