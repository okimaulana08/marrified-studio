<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-cyan-500/15 border border-cyan-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Hitung Mundur</h2>
            <p class="text-[11px] text-white/40 mt-0.5">
                Tampilkan timer otomatis menuju acara pertama. Tanggal diambil dari tab Events.
            </p>
        </div>
    </div>

    <div>
        <label class="block text-[11px] text-white/50 mb-1">Judul</label>
        <input wire:model="countdown.title" type="text" placeholder="Hitung Mundur"
               class="admin-input w-full px-3 py-2 text-sm @error('countdown.title') border-red-400/50 @enderror">
        @error('countdown.title') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-[11px] text-white/50 mb-1">Pesan Tambahan (opsional)</label>
        <textarea wire:model="countdown.message" rows="3"
                  placeholder="Tunggu kami di hari bahagia..."
                  class="admin-input w-full px-3 py-2 text-sm @error('countdown.message') border-red-400/50 @enderror"></textarea>
        @error('countdown.message') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="glass-sm rounded-xl px-3 py-2.5 text-[11px] text-white/55 leading-relaxed">
        <span class="text-cyan-300/80 font-semibold">Tip.</span>
        Variant section bisa diganti di tab <span class="text-white/80">Section</span> — pilih
        <span class="font-mono text-white/70">digital</span> (4 kotak) atau
        <span class="font-mono text-white/70">minimal</span> (1 baris).
    </div>

    <div class="border-t border-white/8 pt-4 flex justify-end gap-2">
        <button wire:click="discardTab('countdown')"
                wire:confirm="Buang perubahan di tab ini?"
                class="btn-ghost text-xs">Batalkan</button>
        <button wire:click="saveCountdown" wire:loading.attr="disabled" wire:target="saveCountdown"
                class="btn-primary text-xs">
            <span wire:loading.remove wire:target="saveCountdown">Simpan</span>
            <span wire:loading wire:target="saveCountdown">Menyimpan...</span>
        </button>
    </div>
</div>
