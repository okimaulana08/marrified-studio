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

        {{-- Preview Image --}}
        @unless ($isNew)
            @php
                $existingPreview = \App\Support\ThemeAsset::findPreview($slug);
                $previewUrl = null;
                if ($previewImage && method_exists($previewImage, 'temporaryUrl')) {
                    try { $previewUrl = $previewImage->temporaryUrl(); } catch (\Throwable $e) { $previewUrl = null; }
                } elseif ($existingPreview) {
                    $previewUrl = \App\Support\ThemeAsset::url($slug, $existingPreview).'?v='.$previewKey;
                }
            @endphp
            <div>
                <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">Preview Image</label>
                <p class="text-[11px] text-white/40 mb-2">Gambar thumbnail tema yang muncul di list tema. Maks 2 MB · WebP/JPG/PNG.</p>

                <div class="photo-dropzone {{ $previewUrl ? 'photo-dropzone--has-image' : '' }}" style="aspect-ratio: 4 / 3;">
                    <label for="themePreviewUpload" class="photo-dropzone-label">
                        @if ($previewUrl)
                            <img src="{{ $previewUrl }}" alt="Preview" class="photo-dropzone-image">
                            <div class="photo-dropzone-overlay">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                </svg>
                                <span>Ganti preview</span>
                            </div>
                        @else
                            <div class="photo-dropzone-empty">
                                <svg class="w-7 h-7 mb-1.5 text-emerald-400/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                                </svg>
                                <span class="text-xs font-semibold text-white/80">Klik untuk upload preview</span>
                                <span class="text-[10px] text-white/40 mt-0.5">Disarankan rasio 4:3 atau 16:9</span>
                            </div>
                        @endif
                    </label>
                    <input id="themePreviewUpload" type="file" wire:model="previewImage" accept="image/webp,image/jpeg,image/png" class="sr-only">
                </div>
                <div class="flex items-center justify-between mt-1.5 min-h-[18px]">
                    <div wire:loading wire:target="previewImage" class="text-[11px] text-emerald-300/80 flex items-center gap-1.5">
                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Mengunggah...
                    </div>
                    <div class="flex items-center gap-2 ml-auto">
                        @if ($previewImage)
                            <button type="button" wire:click="savePreviewImage"
                                    wire:loading.attr="disabled" wire:target="savePreviewImage"
                                    class="btn-primary text-[11px] py-1 px-2.5">
                                <span wire:loading.remove wire:target="savePreviewImage">Simpan preview</span>
                                <span wire:loading wire:target="savePreviewImage">Menyimpan...</span>
                            </button>
                        @endif
                        @if ($existingPreview && ! $previewImage)
                            <button type="button" wire:click="removePreviewImage"
                                    wire:confirm="Hapus preview image?"
                                    class="text-[11px] text-red-300/70 hover:text-red-300 inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Hapus
                            </button>
                        @endif
                    </div>
                </div>
                @error('previewImage') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
        @endunless

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
