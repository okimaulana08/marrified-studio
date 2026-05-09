<div class="space-y-4">
    <div>
        <h2 class="text-sm font-semibold text-white/80 uppercase tracking-widest mb-1">Section Variants</h2>
        <p class="text-xs text-white/30">
            Pilih variant default per section type untuk tema ini.
        </p>
    </div>

    @foreach ([
        'cover'     => ['label' => 'Cover',     'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
        'quotes'    => ['label' => 'Quotes',    'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
        'couple'    => ['label' => 'Couple',    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        'event'     => ['label' => 'Event',     'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        'gallery'   => ['label' => 'Gallery',   'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
        'gift'      => ['label' => 'Gift',      'icon' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7'],
        'rsvp'      => ['label' => 'RSVP',      'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'guestbook' => ['label' => 'Guestbook', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    ] as $type => $meta)
        @php $options = $variantOptions[$type] ?? []; @endphp
        <div class="glass-sm rounded-xl px-4 py-3 flex items-center gap-4">
            <div class="w-7 h-7 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}"/>
                </svg>
            </div>
            <div class="w-20 flex-shrink-0">
                <p class="text-sm font-medium text-white/80">{{ $meta['label'] }}</p>
                <p class="text-[10px] text-white/30 font-mono">{{ $type }}</p>
            </div>
            <div class="flex-1">
                @if ($options)
                    <div class="relative">
                        <select wire:model="variants.{{ $type }}"
                                class="admin-select w-full px-3 py-2 pr-8 text-sm">
                            @foreach ($options as $variant)
                                <option value="{{ $variant }}">{{ $variant }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                @else
                    <div class="px-3 py-2 bg-amber-400/10 border border-amber-400/20 rounded-lg text-xs text-amber-300/70">
                        Tidak ada variant di <span class="font-mono">sections/{{ $type }}/</span>
                    </div>
                @endif
            </div>
            <div class="w-24 text-right">
                @php $currentVariant = $variants->{$type} ?? 'default'; @endphp
                <span class="text-xs font-mono px-2 py-0.5 bg-white/5 border border-white/10 rounded text-white/40">
                    {{ $currentVariant }}
                </span>
            </div>
        </div>
    @endforeach
</div>
