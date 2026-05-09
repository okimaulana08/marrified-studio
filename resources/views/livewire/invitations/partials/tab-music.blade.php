@php
    $tracks = $music->listTracks();
    $selected = $music->musicTrackId;
@endphp

<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-violet-500/15 border border-violet-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-violet-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3zm12-3c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Backsound Musik</h2>
            <p class="text-[11px] text-white/40 mt-0.5">
                Pilih satu track yang akan otomatis play saat tamu klik "Open Invitation".
            </p>
        </div>
    </div>

    @if ($tracks->isEmpty())
        <div class="glass-sm rounded-xl p-8 text-center">
            <p class="text-sm text-white/60 mb-1">Library musik masih kosong.</p>
            <p class="text-[11px] text-white/40">Hubungi admin untuk meng-upload track ke library.</p>
        </div>
    @else
        <div class="space-y-2">
            {{-- "No music" option --}}
            <label class="flex items-center gap-3 glass-sm rounded-xl p-3 cursor-pointer
                          {{ $selected === null ? 'ring-1 ring-emerald-400/40 bg-emerald-500/8' : '' }}">
                <input type="radio" wire:model.live="music.musicTrackId" value=""
                       class="w-4 h-4 accent-emerald-500">
                <div class="flex-1">
                    <p class="text-sm text-white/80">Tanpa musik</p>
                    <p class="text-[11px] text-white/40">Undangan render tanpa backsound.</p>
                </div>
            </label>

            @foreach ($tracks as $track)
                @php $isSelected = $selected === $track->id; @endphp
                <label wire:key="track-option-{{ $track->id }}"
                       class="flex items-center gap-3 glass-sm rounded-xl p-3 cursor-pointer transition-all
                              {{ $isSelected ? 'ring-1 ring-emerald-400/40 bg-emerald-500/8' : 'hover:bg-white/[0.04]' }}">
                    <input type="radio" wire:model.live="music.musicTrackId" value="{{ $track->id }}"
                           class="w-4 h-4 accent-emerald-500">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-white/85 truncate">{{ $track->title }}</p>
                        <p class="text-[11px] text-white/40 truncate">
                            {{ $track->artist ?: '—' }}
                            @if ($track->duration_seconds)
                                &middot; {{ floor($track->duration_seconds / 60) }}:{{ str_pad((string) ($track->duration_seconds % 60), 2, '0', STR_PAD_LEFT) }}
                            @endif
                        </p>
                    </div>
                    <audio controls preload="none"
                           src="{{ \Illuminate\Support\Facades\Storage::disk('music_assets')->url($track->file_path) }}"
                           class="h-8 max-w-[200px] flex-shrink-0"></audio>
                </label>
            @endforeach
        </div>

        <div class="flex items-center justify-between border-t border-white/8 pt-4">
            <p class="text-[11px] text-white/40">
                Track akan otomatis play setelah tamu klik tombol "Open Invitation". Loop dan ada tombol mute.
            </p>
            <div class="flex items-center gap-2">
                <button wire:click="discardTab('music')"
                        wire:confirm="Buang perubahan di tab ini?"
                        class="btn-ghost text-xs">Batalkan</button>
                <button wire:click="saveMusic" wire:loading.attr="disabled" wire:target="saveMusic"
                        class="btn-primary text-xs">
                    <span wire:loading.remove wire:target="saveMusic">Simpan</span>
                    <span wire:loading wire:target="saveMusic">Menyimpan...</span>
                </button>
            </div>
        </div>
    @endif
</div>
