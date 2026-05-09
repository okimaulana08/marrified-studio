<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Gift Accounts</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Bank/e-wallet untuk amplop digital</p>
        </div>
    </div>

    @if (empty($gift->rows))
        <div class="glass-sm rounded-xl p-8 text-center text-white/40 text-sm">
            Belum ada akun. Klik tombol di bawah untuk menambah rekening bank atau e-wallet.
        </div>
    @endif

    <div class="space-y-3">
        @foreach ($gift->rows as $i => $row)
            <div wire:key="gift-row-{{ $i }}" class="glass-sm rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase tracking-widest text-emerald-400/70 font-bold">
                        Akun #{{ $i + 1 }}
                    </span>
                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="moveGiftUp({{ $i }})"
                                @disabled($i === 0)
                                class="p-1 text-white/40 hover:text-white {{ $i === 0 ? 'opacity-30 cursor-not-allowed' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <button type="button" wire:click="moveGiftDown({{ $i }})"
                                @disabled($i === count($gift->rows) - 1)
                                class="p-1 text-white/40 hover:text-white {{ $i === count($gift->rows) - 1 ? 'opacity-30 cursor-not-allowed' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <button type="button" wire:click="removeGiftRow({{ $i }})"
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
                        <select wire:model="gift.rows.{{ $i }}.type" class="admin-select w-full px-2 py-1.5 text-xs">
                            <option value="bank">Bank</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-span-8">
                        <label class="block text-[11px] text-white/50 mb-1">Nama Bank / Provider *</label>
                        <input wire:model="gift.rows.{{ $i }}.bank_name" type="text"
                               placeholder="BCA, Mandiri, GoPay, OVO..."
                               class="admin-input w-full px-2 py-1.5 text-xs @error("gift.rows.$i.bank_name") border-red-400/50 @enderror">
                        @error("gift.rows.$i.bank_name") <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-6">
                        <label class="block text-[11px] text-white/50 mb-1">Nomor Rekening *</label>
                        <input wire:model="gift.rows.{{ $i }}.account_number" type="text"
                               class="admin-input w-full px-2 py-1.5 text-xs font-mono @error("gift.rows.$i.account_number") border-red-400/50 @enderror">
                        @error("gift.rows.$i.account_number") <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-6">
                        <label class="block text-[11px] text-white/50 mb-1">Atas Nama *</label>
                        <input wire:model="gift.rows.{{ $i }}.account_name" type="text"
                               class="admin-input w-full px-2 py-1.5 text-xs @error("gift.rows.$i.account_name") border-red-400/50 @enderror">
                        @error("gift.rows.$i.account_name") <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between border-t border-white/8 pt-4">
        <button type="button" wire:click="addGiftRow" class="btn-ghost text-xs flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Akun
        </button>
        <div class="flex items-center gap-2">
            <button wire:click="discardTab('gift')"
                    wire:confirm="Buang semua perubahan di tab ini?"
                    class="btn-ghost text-xs">Batalkan</button>
            <button wire:click="saveGift" wire:loading.attr="disabled" wire:target="saveGift"
                    class="btn-primary text-xs">
                <span wire:loading.remove wire:target="saveGift">Simpan Gift</span>
                <span wire:loading wire:target="saveGift">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
