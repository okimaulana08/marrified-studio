<div class="space-y-5">
    {{-- Section header --}}
    <div class="flex items-center gap-2.5 mb-1">
        <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Informasi Dasar</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Identitas tema dalam manifest</p>
        </div>
    </div>

    <div class="border-t border-white/8 pt-4 space-y-5">
        {{-- Slug --}}
        <div>
            <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">
                Slug <span class="text-emerald-400">*</span>
            </label>
            @if ($isNew)
                <input wire:model="basic.slug" type="text"
                       class="admin-input w-full px-3 py-2.5 text-sm font-mono @error('basic.slug') border-red-400/50 @enderror"
                       placeholder="contoh: watercolor-lush">
            @else
                <input type="text" value="{{ $basic->slug }}" readonly
                       class="admin-input w-full px-3 py-2.5 text-sm font-mono">
            @endif
            @error('basic.slug')
                <p class="text-xs text-red-400 mt-1.5 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
            <p class="text-[11px] text-white/25 mt-1.5">
                Lowercase, huruf/angka/tanda hubung. Tidak bisa diubah setelah dibuat.
            </p>
        </div>

        {{-- Name --}}
        <div>
            <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">
                Nama Tema <span class="text-emerald-400">*</span>
            </label>
            <input wire:model="basic.name" type="text"
                   class="admin-input w-full px-3 py-2.5 text-sm @error('basic.name') border-red-400/50 @enderror"
                   placeholder="Watercolor Lush">
            @error('basic.name')
                <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">Deskripsi</label>
            <textarea wire:model="basic.description" rows="3"
                      class="admin-input w-full px-3 py-2.5 text-sm resize-none @error('basic.description') border-red-400/50 @enderror"
                      placeholder="Deskripsi singkat tema ini..."></textarea>
            @error('basic.description')
                <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Premium toggle --}}
        <div class="glass-sm rounded-xl px-4 py-3 flex items-center gap-3">
            @php
                $toggleStyle = $basic->isPremium
                    ? 'background: linear-gradient(135deg, #fcd34d 0%, #f59e0b 100%)'
                    : 'background: rgba(255,255,255,0.1)';
            @endphp
            <button type="button" wire:click="$toggle('basic.isPremium')" class="relative inline-flex h-5 w-9 items-center rounded-full transition-all flex-shrink-0 {{ $basic->isPremium ? 'shadow-lg shadow-amber-500/30' : '' }}" @style([$toggleStyle])>
                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-md transition-transform {{ $basic->isPremium ? 'translate-x-4' : 'translate-x-0.5' }}"></span>
            </button>
            <div class="flex-1">
                <p class="text-sm font-medium text-white/90">Tema Premium</p>
                <p class="text-[11px] text-white/40">Akan ditandai dengan badge Premium di list</p>
            </div>
            @if ($basic->isPremium)
                <span class="px-2.5 py-1 text-xs font-bold rounded-full text-amber-900"
                      style="background: linear-gradient(135deg, #fcd34d 0%, #f59e0b 100%); box-shadow: 0 2px 8px -2px rgba(251,191,36,0.5);">
                    Premium
                </span>
            @endif
        </div>
    </div>
</div>
