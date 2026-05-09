@php
    $count = count($gallery->images);
    $max = \App\Livewire\Invitations\Forms\GalleryForm::MAX_IMAGES;
    $remaining = max(0, $max - $count);
@endphp

<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-pink-500/15 border border-pink-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-pink-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Gallery</h2>
            <p class="text-[11px] text-white/40 mt-0.5">
                Foto pre-wedding atau galeri acara &middot;
                <span class="text-emerald-300/80">{{ $count }}/{{ $max }}</span>
            </p>
        </div>
    </div>

    {{-- Upload area --}}
    <div class="glass-sm rounded-xl p-4">
        @if ($remaining > 0)
            <label for="galleryUpload" class="block">
                <div class="border-2 border-dashed border-white/15 hover:border-emerald-400/45 transition-colors rounded-xl p-6 text-center cursor-pointer">
                    <svg class="w-9 h-9 mx-auto mb-2 text-emerald-400/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <p class="text-sm font-semibold text-white/80">Klik untuk pilih foto</p>
                    <p class="text-[11px] text-white/40 mt-0.5">
                        JPG / PNG / WebP &middot; maks 5 MB per foto &middot; bisa pilih multiple
                    </p>
                </div>
            </label>
            <input id="galleryUpload" type="file" wire:model="newGalleryPhotos" multiple accept="image/jpeg,image/png,image/webp" class="sr-only">

            @if (! empty($newGalleryPhotos))
                <div class="mt-3 flex items-center justify-between">
                    <p class="text-xs text-emerald-300/80">
                        {{ count($newGalleryPhotos) }} foto dipilih.
                    </p>
                    <button type="button" wire:click="uploadGalleryPhotos"
                            wire:loading.attr="disabled" wire:target="uploadGalleryPhotos,newGalleryPhotos"
                            class="btn-primary text-xs">
                        <span wire:loading.remove wire:target="uploadGalleryPhotos">Upload</span>
                        <span wire:loading wire:target="uploadGalleryPhotos">Mengunggah...</span>
                    </button>
                </div>
            @endif

            <div wire:loading wire:target="newGalleryPhotos" class="mt-2 text-[11px] text-emerald-300/80 flex items-center gap-1.5">
                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memuat foto...
            </div>

            @error('newGalleryPhotos.*') <p class="text-xs text-red-400 mt-2">{{ $message }}</p> @enderror
            @error('newGalleryPhotos') <p class="text-xs text-red-400 mt-2">{{ $message }}</p> @enderror
        @else
            <div class="rounded-xl p-4 bg-amber-400/10 border border-amber-400/20 text-center">
                <p class="text-sm text-amber-200">
                    Sudah maksimal {{ $max }} foto. Hapus dulu yang ada untuk upload baru.
                </p>
            </div>
        @endif
    </div>

    {{-- Thumbnail grid --}}
    @if ($count > 0)
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
            @foreach ($gallery->images as $i => $path)
                @php $url = \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($path); @endphp
                <div wire:key="gallery-thumb-{{ $i }}-{{ md5($path) }}"
                     class="relative group aspect-square overflow-hidden rounded-lg ring-1 ring-white/10 bg-white/5">
                    <img src="{{ $url }}" alt="Gallery #{{ $i + 1 }}" loading="lazy"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-1.5">
                        <div class="flex items-center justify-between">
                            <div class="flex gap-0.5">
                                <button type="button" wire:click="moveGalleryUp({{ $i }})"
                                        @disabled($i === 0)
                                        class="p-1 rounded bg-white/15 text-white hover:bg-white/30 {{ $i === 0 ? 'opacity-30 cursor-not-allowed' : '' }}"
                                        title="Naikkan">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                <button type="button" wire:click="moveGalleryDown({{ $i }})"
                                        @disabled($i === $count - 1)
                                        class="p-1 rounded bg-white/15 text-white hover:bg-white/30 {{ $i === $count - 1 ? 'opacity-30 cursor-not-allowed' : '' }}"
                                        title="Turunkan">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                            <button type="button" wire:click="removeGalleryPhoto({{ $i }})"
                                    wire:confirm="Hapus foto ini dari galeri?"
                                    class="p-1 rounded bg-red-500/70 text-white hover:bg-red-500"
                                    title="Hapus">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <span class="absolute top-1 left-1 px-1.5 py-px rounded bg-black/55 text-white text-[10px] font-mono">{{ $i + 1 }}</span>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end pt-2 border-t border-white/8">
            <button wire:click="saveGalleryOrder" wire:loading.attr="disabled" wire:target="saveGalleryOrder"
                    class="btn-ghost text-xs">
                <span wire:loading.remove wire:target="saveGalleryOrder">Simpan urutan</span>
                <span wire:loading wire:target="saveGalleryOrder">Menyimpan...</span>
            </button>
        </div>
    @else
        <div class="glass-sm rounded-xl p-8 text-center text-white/40 text-sm">
            Belum ada foto. Pilih file di atas untuk upload pertama.
        </div>
    @endif
</div>
