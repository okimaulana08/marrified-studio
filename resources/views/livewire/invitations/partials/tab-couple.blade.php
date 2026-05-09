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
                <label class="block text-[11px] text-white/50 mb-1">Foto</label>
                @if ($bridePreview)
                    <div class="mb-2">
                        <img src="{{ $bridePreview }}" alt="Bride"
                             class="w-full aspect-square object-cover rounded-xl ring-1 ring-white/10">
                    </div>
                @endif
                <input type="file" wire:model="bridePhoto" accept="image/*"
                       class="text-xs text-white/60 w-full">
                <div wire:loading wire:target="bridePhoto" class="text-[11px] text-white/40 mt-1">Mengunggah...</div>
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
                <label class="block text-[11px] text-white/50 mb-1">Foto</label>
                @if ($groomPreview)
                    <div class="mb-2">
                        <img src="{{ $groomPreview }}" alt="Groom"
                             class="w-full aspect-square object-cover rounded-xl ring-1 ring-white/10">
                    </div>
                @endif
                <input type="file" wire:model="groomPhoto" accept="image/*"
                       class="text-xs text-white/60 w-full">
                <div wire:loading wire:target="groomPhoto" class="text-[11px] text-white/40 mt-1">Mengunggah...</div>
                @error('groomPhoto') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="border-t border-white/8 pt-4 flex justify-end">
        <button wire:click="saveCouple" wire:loading.attr="disabled" wire:target="saveCouple,bridePhoto,groomPhoto"
                class="btn-primary text-xs">
            <span wire:loading.remove wire:target="saveCouple">Simpan Couple</span>
            <span wire:loading wire:target="saveCouple">Menyimpan...</span>
        </button>
    </div>
</div>
