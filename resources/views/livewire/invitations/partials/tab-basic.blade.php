<div class="space-y-6">
    {{-- Section header --}}
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Informasi Dasar</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Slug, tema, dan tipe religi</p>
        </div>
    </div>

    {{-- Slug --}}
    <div>
        <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">
            Slug URL <span class="text-emerald-400">*</span>
        </label>
        @if ($isNew)
            <input wire:model="basic.slug" type="text"
                   class="admin-input w-full px-3 py-2.5 text-sm font-mono @error('basic.slug') border-red-400/50 @enderror"
                   placeholder="contoh: dewi-raka">
        @else
            <input type="text" value="{{ $basic->slug }}" readonly disabled
                   class="admin-input w-full px-3 py-2.5 text-sm font-mono opacity-60">
        @endif
        @error('basic.slug')
            <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
        @enderror
        <p class="text-[11px] text-white/25 mt-1.5">
            URL final: <span class="font-mono text-emerald-400/70">{{ url('/') }}/{{ $basic->slug ?: '...' }}/&lt;token&gt;</span>.
            @if (! $isNew) <span class="text-white/40">Tidak bisa diubah setelah dibuat.</span> @endif
        </p>
    </div>

    {{-- Theme picker --}}
    <div>
        <label class="block text-xs font-medium text-white/50 mb-2 uppercase tracking-wider">
            Tema <span class="text-emerald-400">*</span>
        </label>

        @if ($isNew)
            <div class="grid grid-cols-2 gap-2">
                @foreach ($themes as $theme)
                    <button type="button"
                            wire:click="$set('basic.themeSlug', '{{ $theme->slug }}')"
                            @class([
                                'glass-sm rounded-xl p-3 text-left transition-all border',
                                'border-emerald-400/40 bg-emerald-500/10' => $basic->themeSlug === $theme->slug,
                                'border-white/8 hover:border-white/20' => $basic->themeSlug !== $theme->slug,
                            ])>
                        <p class="text-sm font-semibold text-white truncate">{{ $theme->name }}</p>
                        <p class="text-[11px] font-mono text-white/40 mt-0.5">{{ $theme->slug }}</p>
                        @if ($theme->isPremium)
                            <span class="inline-block mt-1.5 px-1.5 py-px text-[9px] font-bold tracking-wider rounded-full text-amber-900"
                                  style="background: linear-gradient(135deg, #fcd34d, #f59e0b);">PREMIUM</span>
                        @endif
                    </button>
                @endforeach
            </div>
        @else
            <div class="glass-sm rounded-xl p-3 opacity-60">
                <p class="text-sm font-semibold text-white truncate">{{ $basic->themeSlug }}</p>
                <p class="text-[11px] text-white/40 mt-1">Tema tidak bisa diubah. Hubungi admin untuk ganti tema.</p>
            </div>
        @endif
        @error('basic.themeSlug')
            <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
        @enderror
    </div>

    {{-- Religion --}}
    <div>
        <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">
            Religi <span class="text-emerald-400">*</span>
        </label>
        <select wire:model.live="basic.religionType"
                class="admin-select w-full px-3 py-2.5 pr-8 text-sm @error('basic.religionType') border-red-400/50 @enderror">
            @foreach ($religionOptions as $option)
                <option value="{{ $option->value }}">{{ $option->label() }}</option>
            @endforeach
        </select>
        @error('basic.religionType')
            <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
        @enderror
        <p class="text-[11px] text-white/25 mt-1.5">
            Tab "Religi" akan menampilkan field sesuai pilihan ini (ayat untuk Islam, verse untuk Kristen, dst).
        </p>
    </div>
</div>
