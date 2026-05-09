@php
    $bridePreview = null;
    if ($bridePhoto && method_exists($bridePhoto, 'temporaryUrl')) {
        try { $bridePreview = $bridePhoto->temporaryUrl(); } catch (\Throwable $e) { $bridePreview = null; }
    } elseif ($couple->bridePhotoPath) {
        $bridePreview = \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($couple->bridePhotoPath);
    }

    $groomPreview = null;
    if ($groomPhoto && method_exists($groomPhoto, 'temporaryUrl')) {
        try { $groomPreview = $groomPhoto->temporaryUrl(); } catch (\Throwable $e) { $groomPreview = null; }
    } elseif ($couple->groomPhotoPath) {
        $groomPreview = \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($couple->groomPhotoPath);
    }
@endphp

<div class="space-y-6">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-pink-500/15 border border-pink-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 010 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Couple</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Pengantin pria & wanita</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-5">
        {{-- BRIDE --}}
        <div class="space-y-3">
            <p class="text-[10px] uppercase tracking-widest text-emerald-400/70 font-bold">Mempelai Wanita</p>

            <div>
                <label class="block text-[11px] text-white/50 mb-1">Nama Lengkap *</label>
                <input wire:model="couple.brideName" type="text"
                       class="admin-input w-full px-3 py-2 text-sm @error('couple.brideName') border-red-400/50 @enderror">
                @error('couple.brideName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[11px] text-white/50 mb-1">Panggilan</label>
                <input wire:model="couple.brideNickname" type="text" class="admin-input w-full px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-[11px] text-white/50 mb-1">Orang Tua</label>
                <input wire:model="couple.brideParents" type="text"
                       placeholder="Bapak X & Ibu Y" class="admin-input w-full px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-[11px] text-white/50 mb-1">Instagram</label>
                <input wire:model="couple.brideInstagram" type="text" placeholder="@handle"
                       class="admin-input w-full px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-[11px] text-white/50 mb-1.5">Foto</label>
                <div class="photo-dropzone {{ $bridePreview ? 'photo-dropzone--has-image' : '' }}">
                    <label for="brideUpload" class="photo-dropzone-label">
                        @if ($bridePreview)
                            <img src="{{ $bridePreview }}" alt="Bride preview" class="photo-dropzone-image">
                            <div class="photo-dropzone-overlay">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                </svg>
                                <span>Ganti foto</span>
                            </div>
                        @else
                            <div class="photo-dropzone-empty">
                                <svg class="w-7 h-7 mb-1.5 text-emerald-400/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                                <span class="text-xs font-semibold text-white/80">Klik untuk upload foto</span>
                                <span class="text-[10px] text-white/40 mt-0.5">JPG / PNG / WebP · maks 5 MB</span>
                            </div>
                        @endif
                    </label>
                    <input id="brideUpload" type="file" wire:model="bridePhoto" accept="image/*" class="sr-only">
                </div>
                <div class="flex items-center justify-between mt-1.5 min-h-[18px]">
                    <div wire:loading wire:target="bridePhoto" class="text-[11px] text-emerald-300/80 flex items-center gap-1.5">
                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Mengunggah...
                    </div>
                    @if ($couple->bridePhotoPath || $bridePhoto)
                        <button type="button"
                                wire:click="removeBridePhoto"
                                wire:confirm="Hapus foto mempelai wanita?"
                                wire:loading.attr="disabled"
                                wire:target="removeBridePhoto"
                                class="text-[11px] text-red-300/70 hover:text-red-300 ml-auto inline-flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Hapus foto
                        </button>
                    @endif
                </div>
                @error('bridePhoto') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- GROOM --}}
        <div class="space-y-3">
            <p class="text-[10px] uppercase tracking-widest text-emerald-400/70 font-bold">Mempelai Pria</p>

            <div>
                <label class="block text-[11px] text-white/50 mb-1">Nama Lengkap *</label>
                <input wire:model="couple.groomName" type="text"
                       class="admin-input w-full px-3 py-2 text-sm @error('couple.groomName') border-red-400/50 @enderror">
                @error('couple.groomName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[11px] text-white/50 mb-1">Panggilan</label>
                <input wire:model="couple.groomNickname" type="text" class="admin-input w-full px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-[11px] text-white/50 mb-1">Orang Tua</label>
                <input wire:model="couple.groomParents" type="text"
                       placeholder="Bapak X & Ibu Y" class="admin-input w-full px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-[11px] text-white/50 mb-1">Instagram</label>
                <input wire:model="couple.groomInstagram" type="text" placeholder="@handle"
                       class="admin-input w-full px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-[11px] text-white/50 mb-1.5">Foto</label>
                <div class="photo-dropzone {{ $groomPreview ? 'photo-dropzone--has-image' : '' }}">
                    <label for="groomUpload" class="photo-dropzone-label">
                        @if ($groomPreview)
                            <img src="{{ $groomPreview }}" alt="Groom preview" class="photo-dropzone-image">
                            <div class="photo-dropzone-overlay">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                </svg>
                                <span>Ganti foto</span>
                            </div>
                        @else
                            <div class="photo-dropzone-empty">
                                <svg class="w-7 h-7 mb-1.5 text-emerald-400/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                                <span class="text-xs font-semibold text-white/80">Klik untuk upload foto</span>
                                <span class="text-[10px] text-white/40 mt-0.5">JPG / PNG / WebP · maks 5 MB</span>
                            </div>
                        @endif
                    </label>
                    <input id="groomUpload" type="file" wire:model="groomPhoto" accept="image/*" class="sr-only">
                </div>
                <div class="flex items-center justify-between mt-1.5 min-h-[18px]">
                    <div wire:loading wire:target="groomPhoto" class="text-[11px] text-emerald-300/80 flex items-center gap-1.5">
                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Mengunggah...
                    </div>
                    @if ($couple->groomPhotoPath || $groomPhoto)
                        <button type="button"
                                wire:click="removeGroomPhoto"
                                wire:confirm="Hapus foto mempelai pria?"
                                wire:loading.attr="disabled"
                                wire:target="removeGroomPhoto"
                                class="text-[11px] text-red-300/70 hover:text-red-300 ml-auto inline-flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Hapus foto
                        </button>
                    @endif
                </div>
                @error('groomPhoto') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="border-t border-white/8 pt-4 flex justify-end gap-2">
        <button wire:click="discardTab('couple')"
                wire:confirm="Buang semua perubahan di tab ini?"
                class="btn-ghost text-xs">Batalkan</button>
        <button wire:click="saveCouple" wire:loading.attr="disabled" wire:target="saveCouple,bridePhoto,groomPhoto"
                class="btn-primary text-xs">
            <span wire:loading.remove wire:target="saveCouple">Simpan Couple</span>
            <span wire:loading wire:target="saveCouple">Menyimpan...</span>
        </button>
    </div>
</div>
