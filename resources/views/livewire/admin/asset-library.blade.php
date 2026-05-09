<div x-data="assetUploadDrop('{{ $slug }}')"
     @dragover.prevent="dragging = true"
     @dragleave.prevent="dragging = false"
     @drop.prevent="handleDrop($event)">

    {{-- Flash --}}
    @if ($flashMessage)
        <div x-data="{ show: true }" x-show="show" x-transition x-cloak
             x-init="setTimeout(() => show = false, 3500)"
             class="mb-3 px-3 py-2 rounded-xl text-xs font-medium glass-strong
                    {{ $flashType === 'error' ? 'border-red-400/30 text-red-300' : 'border-emerald-400/30 text-emerald-300' }}">
            {{ $flashMessage }}
        </div>
    @endif

    {{-- Upload zone --}}
    <div :class="dragging ? 'border-emerald-400/50 bg-emerald-400/5' : 'border-white/15 bg-white/3'"
         class="border-2 border-dashed rounded-2xl p-6 text-center transition-all mb-4">
        <div class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
        </div>
        <p class="text-sm text-white/50 mb-1">Drag &amp; drop atau klik untuk pilih</p>
        <p class="text-xs text-white/25">WebP, SVG, PNG, JPG — maks 10 MB</p>

        <label class="mt-4 inline-block cursor-pointer px-4 py-2 glass-sm text-white/60 hover:text-white text-xs font-medium rounded-xl transition-all">
            Pilih File
            <input type="file" wire:model="uploadedFiles" multiple
                   accept=".webp,.svg,.png,.jpg,.jpeg,.json"
                   class="sr-only" @change="dragging = false">
        </label>

        {{-- Upload progress --}}
        <div wire:loading wire:target="uploadedFiles" class="mt-3">
            <div class="inline-flex items-center gap-2 text-xs text-emerald-400/70">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Mengupload...
            </div>
        </div>

        @if (!empty($uploadedFiles))
            <div class="mt-3 text-xs text-white/40">{{ count($uploadedFiles) }} file dipilih</div>
            <button wire:click="uploadFiles" wire:loading.attr="disabled"
                    class="mt-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-semibold rounded-xl disabled:opacity-40 transition-colors">
                <span wire:loading.remove wire:target="uploadFiles">Upload {{ count($uploadedFiles) }} File</span>
                <span wire:loading wire:target="uploadFiles">Mengupload...</span>
            </button>
        @endif
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-medium text-white/40">{{ count($assets) }} assets</span>
        <button wire:click="publishAssets" wire:loading.attr="disabled"
                class="text-xs text-white/40 hover:text-white/70 flex items-center gap-1.5 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <span wire:loading.remove wire:target="publishAssets">Re-publish</span>
            <span wire:loading wire:target="publishAssets">Publishing...</span>
        </button>
    </div>

    {{-- Asset grid --}}
    @if (empty($assets))
        <div class="text-center py-10 text-white/25 text-sm">
            Belum ada asset. Upload file di atas.
        </div>
    @else
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
            @foreach ($assets as $filename)
                @php
                    $ext     = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['webp', 'png', 'jpg', 'jpeg']);
                    $isSvg   = $ext === 'svg';
                    $pubUrl  = \App\Support\ThemeAsset::url($slug, $filename);
                    $srcPath = resource_path("themes/{$slug}/assets/{$filename}");
                    $extBadge = match($ext) {
                        'svg'  => 'bg-blue-400/20 text-blue-300 border border-blue-400/20',
                        'webp' => 'bg-emerald-400/20 text-emerald-300 border border-emerald-400/20',
                        'json' => 'bg-purple-400/20 text-purple-300 border border-purple-400/20',
                        default => 'bg-white/8 text-white/40 border border-white/10',
                    };
                @endphp
                <div class="group relative glass-sm rounded-xl overflow-hidden hover:bg-white/8 transition-all">
                    {{-- Thumbnail --}}
                    <div class="h-20 flex items-center justify-center bg-white/3">
                        @if ($isImage)
                            <img src="{{ $pubUrl }}" alt="{{ $filename }}" class="h-full w-full object-cover">
                        @elseif ($isSvg && file_exists($srcPath))
                            <div class="w-9 h-9 text-white/50">
                                {!! file_get_contents($srcPath) !!}
                            </div>
                        @else
                            <svg class="w-8 h-8 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="p-2">
                        <p class="text-[10px] text-white/60 truncate font-medium" title="{{ $filename }}">
                            {{ $filename }}
                        </p>
                        <span class="text-[9px] px-1 py-0.5 rounded-full font-medium uppercase {{ $extBadge }}">
                            {{ $ext }}
                        </span>
                    </div>

                    {{-- Delete button --}}
                    <button wire:click="confirmDelete('{{ $filename }}')"
                            class="absolute top-1.5 right-1.5 w-5 h-5 bg-red-500/80 hover:bg-red-500 text-white rounded-full
                                   opacity-0 group-hover:opacity-100 transition-opacity
                                   flex items-center justify-center"
                            title="Hapus">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Delete confirm modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="glass-strong rounded-2xl w-full max-w-sm p-6 shadow-2xl">
                <h3 class="text-base font-semibold text-white mb-1">Hapus Asset</h3>
                <p class="text-sm text-white/50 mb-3">
                    Yakin ingin menghapus
                    <span class="font-mono text-white/80">{{ $deleteTarget }}</span>?
                </p>

                @if (!empty($deleteUsages))
                    <div class="p-3 bg-amber-400/10 border border-amber-400/20 rounded-xl mb-3">
                        <p class="text-xs font-medium text-amber-300 mb-1">File ini dipakai di:</p>
                        @foreach ($deleteUsages as $usage)
                            <p class="text-xs text-amber-400/70 font-mono">{{ $usage }}</p>
                        @endforeach
                        <p class="text-xs text-amber-300/60 mt-1">Menghapus akan membuat referensi tersebut rusak.</p>
                    </div>
                @endif

                <div class="flex gap-3 justify-end">
                    <button wire:click="closeDeleteModal"
                            class="px-4 py-2 glass-sm text-white/60 hover:text-white text-sm font-medium rounded-xl transition-all">
                        Batal
                    </button>
                    <button wire:click="deleteAsset"
                            class="px-4 py-2 bg-red-500/80 hover:bg-red-500 text-white text-sm font-medium rounded-xl transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function assetUploadDrop(slug) {
    return {
        dragging: false,
        handleDrop(e) {
            this.dragging = false;
        }
    }
}
</script>
