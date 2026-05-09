<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Events</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Akad, resepsi, dan acara lain</p>
        </div>
    </div>

    @if (empty($events->rows))
        <div class="glass-sm rounded-xl p-8 text-center text-white/40 text-sm">
            Belum ada event. Klik tombol di bawah untuk menambahkan akad/resepsi.
        </div>
    @endif

    <div class="space-y-3">
        @foreach ($events->rows as $i => $row)
            <div wire:key="event-row-{{ $i }}" class="glass-sm rounded-xl p-4 space-y-3 relative">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase tracking-widest text-emerald-400/70 font-bold">
                        Event #{{ $i + 1 }}
                    </span>
                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="moveEventUp({{ $i }})"
                                @disabled($i === 0)
                                class="p-1 text-white/40 hover:text-white {{ $i === 0 ? 'opacity-30 cursor-not-allowed' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <button type="button" wire:click="moveEventDown({{ $i }})"
                                @disabled($i === count($events->rows) - 1)
                                class="p-1 text-white/40 hover:text-white {{ $i === count($events->rows) - 1 ? 'opacity-30 cursor-not-allowed' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <button type="button" wire:click="removeEventRow({{ $i }})"
                                class="p-1 text-red-300/60 hover:text-red-300 ml-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-4">
                        <label class="block text-[11px] text-white/50 mb-1">Tipe</label>
                        <select wire:model="events.rows.{{ $i }}.type" class="admin-select w-full px-2 py-1.5 text-xs">
                            <option value="akad">Akad</option>
                            <option value="resepsi">Resepsi</option>
                            <option value="pemberkatan">Pemberkatan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-span-8">
                        <label class="block text-[11px] text-white/50 mb-1">Nama Acara *</label>
                        <input wire:model="events.rows.{{ $i }}.name" type="text"
                               class="admin-input w-full px-2 py-1.5 text-xs @error("events.rows.$i.name") border-red-400/50 @enderror">
                        @error("events.rows.$i.name") <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-7">
                        <label class="block text-[11px] text-white/50 mb-1">Tanggal *</label>
                        <input wire:model="events.rows.{{ $i }}.date" type="date"
                               class="admin-input w-full px-2 py-1.5 text-xs @error("events.rows.$i.date") border-red-400/50 @enderror">
                        @error("events.rows.$i.date") <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-5">
                        <label class="block text-[11px] text-white/50 mb-1">Jam</label>
                        <input wire:model="events.rows.{{ $i }}.time" type="time"
                               class="admin-input w-full px-2 py-1.5 text-xs">
                    </div>
                    <div class="col-span-12">
                        <label class="block text-[11px] text-white/50 mb-1">Nama Venue *</label>
                        <input wire:model="events.rows.{{ $i }}.venue_name" type="text"
                               class="admin-input w-full px-2 py-1.5 text-xs @error("events.rows.$i.venue_name") border-red-400/50 @enderror">
                        @error("events.rows.$i.venue_name") <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-12">
                        <label class="block text-[11px] text-white/50 mb-1">Alamat Venue</label>
                        <textarea wire:model="events.rows.{{ $i }}.venue_address" rows="2"
                                  class="admin-input w-full px-2 py-1.5 text-xs resize-none"></textarea>
                    </div>
                    <div class="col-span-12">
                        <label class="block text-[11px] text-white/50 mb-1">Google Maps URL</label>
                        <input wire:model="events.rows.{{ $i }}.maps_url" type="url"
                               placeholder="https://maps.google.com/?q=..."
                               class="admin-input w-full px-2 py-1.5 text-xs @error("events.rows.$i.maps_url") border-red-400/50 @enderror">
                        @error("events.rows.$i.maps_url") <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between border-t border-white/8 pt-4">
        <button type="button" wire:click="addEventRow" class="btn-ghost text-xs flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Event
        </button>
        <button wire:click="saveEvents" wire:loading.attr="disabled" wire:target="saveEvents"
                class="btn-primary text-xs">
            <span wire:loading.remove wire:target="saveEvents">Simpan Events</span>
            <span wire:loading wire:target="saveEvents">Menyimpan...</span>
        </button>
    </div>
</div>
