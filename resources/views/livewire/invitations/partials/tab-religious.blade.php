<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-indigo-500/15 border border-indigo-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Teks Religi</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Religi: <span class="text-emerald-300">{{ $religionLabel }}</span></p>
        </div>
    </div>

    @if (empty($religiousFieldKeys))
        <div class="glass-sm rounded-xl p-8 text-center text-white/40 text-sm">
            Religi diset ke <strong>"Tanpa teks religius"</strong>. Tab ini akan kosong.
            Ubah religi di tab Info untuk mengaktifkan field di sini.
        </div>
    @else
        <div class="space-y-4">
            @foreach ($religiousFieldKeys as $key)
                <div>
                    <label class="block text-[11px] text-white/50 mb-1.5 uppercase tracking-wider">
                        {{ str_replace('_', ' ', $key) }}
                    </label>
                    <textarea wire:model="religious.values.{{ $key }}" rows="3"
                              {{ $key === 'ayat' ? 'dir=rtl' : '' }}
                              class="admin-input w-full px-3 py-2 text-sm resize-y {{ $key === 'ayat' ? 'font-serif text-base leading-loose' : '' }}"
                              placeholder="..."></textarea>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end border-t border-white/8 pt-4">
            <button wire:click="saveReligious" wire:loading.attr="disabled" wire:target="saveReligious"
                    class="btn-primary text-xs">
                <span wire:loading.remove wire:target="saveReligious">Simpan Teks Religi</span>
                <span wire:loading wire:target="saveReligious">Menyimpan...</span>
            </button>
        </div>
    @endif
</div>
