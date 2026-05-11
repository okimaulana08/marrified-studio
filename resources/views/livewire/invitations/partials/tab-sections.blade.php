<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Sections</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Toggle, ganti variant, atur urutan halaman</p>
        </div>
    </div>

    <p class="text-[11px] text-white/40 -mt-2">
        <svg class="inline w-3 h-3 mr-1 -mt-px text-emerald-400/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/>
        </svg>
        Drag handle untuk reorder, atau pakai tombol panah. Toggle untuk aktif/nonaktif.
    </p>

    <div class="space-y-2" wire:ignore.self
         x-data="sectionsSortable()"
         x-init="init()"
         data-sortable-host>
        @foreach ($sections->rows as $i => $row)
            @php
                $variants = $variantOptions[$row['type']] ?? [];
                $bgSource = $row['bg_source'] ?? '';
                $bgHasOverride = $bgSource !== '' && $bgSource !== 'default';
                $bgUpload = $sectionBgUpload[$i] ?? null;
                $bgUploadPreview = null;
                if ($bgUpload && method_exists($bgUpload, 'temporaryUrl')) {
                    try { $bgUploadPreview = $bgUpload->temporaryUrl(); } catch (\Throwable $e) {}
                } elseif ($bgSource === 'upload' && ! empty($row['bg_path'])) {
                    $bgUploadPreview = \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($row['bg_path']);
                }
            @endphp
            <div wire:key="section-row-{{ $row['id'] }}"
                 data-id="{{ $row['id'] }}"
                 x-data="{ bgOpen: false }"
                 class="glass-sm rounded-xl {{ $row['enabled'] ? '' : 'opacity-50' }}">
                <div class="px-2.5 py-2.5 grid grid-cols-12 gap-2 items-center">
                    <div class="col-span-1 flex items-center justify-center gap-1">
                        <span data-drag-handle
                              title="Geser untuk urut"
                              class="cursor-grab active:cursor-grabbing text-white/30 hover:text-white/65 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="6" r="1"/><circle cx="15" cy="6" r="1"/>
                                <circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/>
                                <circle cx="9" cy="18" r="1"/><circle cx="15" cy="18" r="1"/>
                            </svg>
                        </span>
                    </div>
                    <div class="col-span-1 flex justify-center">
                        <label class="flex items-center cursor-pointer" title="Aktif">
                            <input type="checkbox" wire:model.live="sections.rows.{{ $i }}.enabled"
                                   class="w-4 h-4 rounded bg-white/5 border-white/15 accent-emerald-500">
                        </label>
                    </div>
                    <div class="col-span-3">
                        <p class="text-sm text-white/85 capitalize">{{ $row['type'] }}</p>
                        <p class="text-[10px] text-white/30 font-mono">
                            #{{ $i + 1 }}
                            @if ($bgHasOverride)
                                <span class="ml-1 text-amber-300/70" title="Punya custom background">· bg</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-span-4">
                        @if (count($variants) > 0)
                            <select wire:model="sections.rows.{{ $i }}.variant"
                                    class="admin-select w-full px-2 py-1.5 text-xs">
                                @foreach ($variants as $v)
                                    <option value="{{ $v }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" value="{{ $row['variant'] }}" disabled
                                   class="admin-input w-full px-2 py-1.5 text-xs opacity-60 italic"
                                   title="No blade variants found for this section type.">
                        @endif
                    </div>
                    <div class="col-span-3 flex items-center justify-end gap-1">
                        <button type="button" x-on:click="bgOpen = !bgOpen"
                                :title="bgOpen ? 'Tutup pengaturan background' : 'Buka pengaturan background'"
                                class="p-1 text-white/40 hover:text-white {{ $bgHasOverride ? 'text-amber-300/75' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </button>
                        <button type="button" wire:click="moveSectionUp({{ $i }})"
                                @disabled($i === 0)
                                class="p-1 text-white/40 hover:text-white {{ $i === 0 ? 'opacity-30 cursor-not-allowed' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <button type="button" wire:click="moveSectionDown({{ $i }})"
                                @disabled($i === count($sections->rows) - 1)
                                class="p-1 text-white/40 hover:text-white {{ $i === count($sections->rows) - 1 ? 'opacity-30 cursor-not-allowed' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Background override panel (collapsible) --}}
                <div x-show="bgOpen" x-collapse class="border-t border-white/8 px-3 py-3 space-y-2.5 bg-white/[0.015]">
                    <p class="text-[10px] uppercase tracking-widest text-amber-300/70 font-bold flex items-center gap-1.5">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Background Override — Page "{{ $row['type'] }}"
                    </p>
                    <p class="text-[11px] text-white/40 -mt-1">
                        Ganti background untuk halaman ini saja. Pilih dari foto pengantin, galeri, atau upload baru.
                    </p>

                    <div>
                        <label class="block text-[10px] text-white/50 mb-1 uppercase tracking-wider">Sumber</label>
                        <select wire:model.live="sections.rows.{{ $i }}.bg_source"
                                class="admin-select w-full px-2 py-1.5 text-xs">
                            <option value="">— Default theme —</option>
                            @if ($hasCouplePhoto['bride'])
                                <option value="couple_bride">Foto Mempelai Wanita</option>
                            @endif
                            @if ($hasCouplePhoto['groom'])
                                <option value="couple_groom">Foto Mempelai Pria</option>
                            @endif
                            @if (count($galleryImages) > 0)
                                <option value="gallery">Dari Galeri ({{ count($galleryImages) }} foto)</option>
                            @endif
                            <option value="upload">Upload foto khusus</option>
                        </select>
                        @if (! $hasCouplePhoto['bride'] && ! $hasCouplePhoto['groom'])
                            <p class="text-[10px] text-white/30 mt-1 italic">Upload foto pengantin di tab Couple untuk mengaktifkan pilihan tersebut.</p>
                        @endif
                    </div>

                    {{-- Gallery picker --}}
                    @if ($bgSource === 'gallery' && count($galleryImages) > 0)
                        <div>
                            <label class="block text-[10px] text-white/50 mb-1 uppercase tracking-wider">Pilih Foto Galeri</label>
                            <div class="grid grid-cols-6 gap-1.5">
                                @foreach ($galleryImages as $idx => $imgPath)
                                    @php $imgUrl = \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($imgPath); @endphp
                                    <button type="button"
                                            wire:click="$set('sections.rows.{{ $i }}.bg_gallery_index', {{ $idx }})"
                                            @class([
                                                'aspect-square rounded-md overflow-hidden border-2 transition-all',
                                                'border-emerald-400/60' => (int) ($row['bg_gallery_index'] ?? 0) === $idx,
                                                'border-transparent hover:border-white/20' => (int) ($row['bg_gallery_index'] ?? 0) !== $idx,
                                            ])>
                                        <img src="{{ $imgUrl }}" class="w-full h-full object-cover" alt="">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Upload picker --}}
                    @if ($bgSource === 'upload')
                        <div>
                            <label class="block text-[10px] text-white/50 mb-1 uppercase tracking-wider">Foto Background Khusus</label>
                            <div class="photo-dropzone {{ $bgUploadPreview ? 'photo-dropzone--has-image' : '' }}" style="aspect-ratio: 16 / 9;">
                                <label for="bgUpload-{{ $i }}" class="photo-dropzone-label">
                                    @if ($bgUploadPreview)
                                        <img src="{{ $bgUploadPreview }}" alt="Background preview" class="photo-dropzone-image">
                                        <div class="photo-dropzone-overlay">
                                            <span>Ganti foto</span>
                                        </div>
                                    @else
                                        <div class="photo-dropzone-empty">
                                            <svg class="w-6 h-6 mb-1 text-amber-300/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                            </svg>
                                            <span class="text-xs font-semibold text-white/80">Upload background</span>
                                            <span class="text-[10px] text-white/40 mt-0.5">JPG / PNG / WebP · maks 5 MB</span>
                                        </div>
                                    @endif
                                </label>
                                <input id="bgUpload-{{ $i }}" type="file"
                                       wire:model="sectionBgUpload.{{ $i }}"
                                       accept="image/*" class="sr-only">
                            </div>
                            <div class="flex justify-end gap-2 mt-1.5">
                                <div wire:loading wire:target="sectionBgUpload.{{ $i }}" class="text-[11px] text-amber-200/80">Mengunggah…</div>
                                @if ($bgUpload)
                                    <button type="button" wire:click="uploadSectionBg({{ $i }})"
                                            class="btn-primary text-[11px] px-2.5 py-1">Simpan foto</button>
                                @endif
                                @if ($bgSource === 'upload' && ! empty($row['bg_path']))
                                    <button type="button" wire:click="removeSectionBg({{ $i }})"
                                            wire:confirm="Hapus foto background ini?"
                                            class="text-[11px] text-red-300/70 hover:text-red-300">Hapus foto</button>
                                @endif
                            </div>
                            @error("sectionBgUpload.{$i}") <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Opacity + Darken + Fit (visible when there IS an override) --}}
                    @if ($bgHasOverride)
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="flex items-center justify-between text-[10px] text-white/50 mb-1 uppercase tracking-wider">
                                    <span>Opacity</span>
                                    <span class="text-amber-300/80 font-mono normal-case">{{ number_format((float) ($row['bg_opacity'] ?? 1.0), 2) }}</span>
                                </label>
                                <div class="flex items-center gap-1.5">
                                    <input type="range"
                                           wire:model.live.debounce.200ms="sections.rows.{{ $i }}.bg_opacity"
                                           min="0" max="1" step="0.05" class="flex-1 min-w-0">
                                    <input type="number"
                                           wire:model.live.debounce.500ms="sections.rows.{{ $i }}.bg_opacity"
                                           min="0" max="1" step="0.05"
                                           class="admin-input w-14 px-1.5 py-1 text-[11px] font-mono text-center">
                                </div>
                                <p class="text-[10px] text-white/30 mt-0.5">Tembus pandang ke background di belakang</p>
                            </div>
                            <div>
                                <label class="flex items-center justify-between text-[10px] text-white/50 mb-1 uppercase tracking-wider">
                                    <span>Darken</span>
                                    <span class="text-amber-300/80 font-mono normal-case">{{ number_format((float) ($row['bg_darken'] ?? 0.0), 2) }}</span>
                                </label>
                                <div class="flex items-center gap-1.5">
                                    <input type="range"
                                           wire:model.live.debounce.200ms="sections.rows.{{ $i }}.bg_darken"
                                           min="0" max="1" step="0.05" class="flex-1 min-w-0">
                                    <input type="number"
                                           wire:model.live.debounce.500ms="sections.rows.{{ $i }}.bg_darken"
                                           min="0" max="1" step="0.05"
                                           class="admin-input w-14 px-1.5 py-1 text-[11px] font-mono text-center">
                                </div>
                                <p class="text-[10px] text-white/30 mt-0.5">Lapisan hitam transparan di atas foto (untuk kontras teks)</p>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[10px] text-white/50 mb-1 uppercase tracking-wider">Fit</label>
                                <div class="flex gap-1.5">
                                    @foreach (['cover' => 'Cover (penuhi area, mungkin crop)', 'contain' => 'Contain (muat semua, mungkin ada ruang)'] as $val => $label)
                                        <button type="button"
                                                wire:click="$set('sections.rows.{{ $i }}.bg_fit', '{{ $val }}')"
                                                @class([
                                                    'flex-1 px-2 py-1 text-[11px] rounded-md border transition-all',
                                                    'bg-emerald-500/20 text-emerald-100 border-emerald-400/40' => ($row['bg_fit'] ?? 'cover') === $val,
                                                    'glass-sm text-white/55 hover:text-white border-transparent' => ($row['bg_fit'] ?? 'cover') !== $val,
                                                ])>
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @once
    <script>
        window.sectionsSortable = function () {
            return {
                instance: null,
                async init() {
                    if (typeof window.loadSortable !== 'function') return;
                    const mod = await window.loadSortable();
                    this.instance = mod.mount(this.$el, {
                        handle: '[data-drag-handle]',
                        onEnd: (ids) => {
                            // Push new order back to the Livewire action, which
                            // re-sorts + persists immediately.
                            this.$wire.call('reorderSections', ids);
                        },
                    });
                },
                destroy() { this.instance?.destroy?.(); },
            };
        };
    </script>
    @endonce

    {{-- Drag visuals --}}
    <style>
        .is-dragging-ghost { opacity: 0.35; transform: scale(0.99); }
        .is-dragging-chosen { box-shadow: 0 12px 32px -8px rgba(232, 62, 140, 0.45); }
        .is-dragging-active { background: rgba(232, 62, 140, 0.08) !important; }
    </style>

    <div class="flex justify-end gap-2 border-t border-white/8 pt-4">
        <button wire:click="discardTab('sections')"
                wire:confirm="Buang semua perubahan di tab ini?"
                class="btn-ghost text-xs">Batalkan</button>
        <button wire:click="saveSections" wire:loading.attr="disabled" wire:target="saveSections"
                class="btn-primary text-xs">
            <span wire:loading.remove wire:target="saveSections">Simpan Sections</span>
            <span wire:loading wire:target="saveSections">Menyimpan...</span>
        </button>
    </div>
</div>
