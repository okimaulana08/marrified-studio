@php
$fontGroups = [
    'Display / Serif' => [
        'Playfair Display',
        'Cormorant Garamond',
        'Libre Baskerville',
        'EB Garamond',
        'Crimson Text',
    ],
    'Sans-Serif' => [
        'Lato',
        'Poppins',
        'Montserrat',
        'Raleway',
        'Josefin Sans',
    ],
    'Script / Kursif' => [
        'Petit Formal Script',
        'Dancing Script',
        'Great Vibes',
        'Parisienne',
        'Sacramento',
        'Alex Brush',
    ],
];
// Avoid $fonts variable collision with the Livewire FontsForm object
$fontGroupMap = $fontGroups;
@endphp

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-white/80 uppercase tracking-widest">Typography</h2>
        <a href="https://fonts.bunny.net" target="_blank"
           class="text-xs text-emerald-400/70 hover:text-emerald-300 transition-colors">
            Bunny Fonts ↗
        </a>
    </div>

    <p class="text-xs text-white/30 -mt-2">
        Semua font di bawah sudah di-preload. Pilih dari daftar atau ketik nama font lain.
    </p>

    {{-- Display Font --}}
    <div x-data="{ font: @js($fonts->display), open: false }"
         x-init="$wire.$watch('fonts.display', v => font = v)">

        <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">Display Font</label>
        <p class="text-xs text-white/25 mb-2">Untuk heading &amp; nama pasangan</p>

        {{-- Select dropdown --}}
        <div class="relative">
            <select wire:model.live="fonts.display"
                    x-on:change="font = $event.target.value"
                    class="admin-select w-full px-3 py-2.5 pr-8 text-sm @error('fonts.display') border-red-400/50 @enderror">
                @foreach ($fontGroupMap as $group => $fontNames)
                    <optgroup label="{{ $group }}">
                        @foreach ($fontNames as $fontName)
                            <option value="{{ $fontName }}">{{ $fontName }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
        @error('fonts.display')
            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
        @enderror

        {{-- Live preview --}}
        <div class="mt-3 p-4 bg-white/5 rounded-xl border border-white/8">
            <p x-text="font" class="text-white/30 text-xs mb-2 font-mono"></p>
            <p :style="'font-family: \'' + font + '\', serif; font-size: 30px; line-height: 1.2;'"
               class="text-white/90">
                The Wedding Of
            </p>
        </div>
    </div>

    {{-- Body Font --}}
    <div x-data="{ font: @js($fonts->body) }"
         x-init="$wire.$watch('fonts.body', v => font = v)">

        <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">Body Font</label>
        <p class="text-xs text-white/25 mb-2">Untuk teks paragraf</p>

        <div class="relative">
            <select wire:model.live="fonts.body"
                    x-on:change="font = $event.target.value"
                    class="admin-select w-full px-3 py-2.5 pr-8 text-sm @error('fonts.body') border-red-400/50 @enderror">
                @foreach ($fontGroupMap as $group => $fontNames)
                    <optgroup label="{{ $group }}">
                        @foreach ($fontNames as $fontName)
                            <option value="{{ $fontName }}">{{ $fontName }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
        @error('fonts.body')
            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
        @enderror

        <div class="mt-3 p-4 bg-white/5 rounded-xl border border-white/8">
            <p x-text="font" class="text-white/30 text-xs mb-2 font-mono"></p>
            <p :style="'font-family: \'' + font + '\'; font-size: 14px; line-height: 1.7;'"
               class="text-white/80">
                Undangan pernikahan kami dengan tulus mengharapkan kehadiran Bapak/Ibu pada acara sakral kami.
            </p>
        </div>
    </div>

    {{-- Script Font --}}
    <div x-data="{ font: @js($fonts->script) }"
         x-init="$wire.$watch('fonts.script', v => font = v)">

        <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">Script Font</label>
        <p class="text-xs text-white/25 mb-2">Untuk kata-kata dekoratif</p>

        <div class="relative">
            <select wire:model.live="fonts.script"
                    x-on:change="font = $event.target.value"
                    class="admin-select w-full px-3 py-2.5 pr-8 text-sm @error('fonts.script') border-red-400/50 @enderror">
                @foreach ($fontGroupMap as $group => $fontNames)
                    <optgroup label="{{ $group }}">
                        @foreach ($fontNames as $fontName)
                            <option value="{{ $fontName }}">{{ $fontName }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
        @error('fonts.script')
            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
        @enderror

        <div class="mt-3 p-4 bg-white/5 rounded-xl border border-white/8">
            <p x-text="font" class="text-white/30 text-xs mb-2 font-mono"></p>
            <p :style="'font-family: \'' + font + '\', cursive; font-size: 34px; line-height: 1.2;'"
               class="text-white/90">
                Raka &amp; Dewi
            </p>
        </div>
    </div>
</div>
