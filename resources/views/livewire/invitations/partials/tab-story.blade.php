<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-rose-500/15 border border-rose-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-rose-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 010 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Cerita / Timeline Cinta</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Momen-momen perjalanan dari awal bertemu sampai sekarang</p>
        </div>
    </div>

    @if (empty($stories->rows))
        <div class="glass-sm rounded-xl p-8 text-center text-white/40 text-sm">
            Belum ada cerita. Klik tombol di bawah untuk menambah momen pertama
            (mis. <span class="text-emerald-300/70">2018 — Pertama bertemu</span>).
        </div>
    @endif

    <div class="space-y-3">
        @foreach ($stories->rows as $i => $row)
            @php
                $existingPhoto = $row['photo_path'] ?? null;
                $tempPhoto = $storyPhotos[$i] ?? null;
                $previewUrl = null;
                if ($tempPhoto && method_exists($tempPhoto, 'temporaryUrl')) {
                    try { $previewUrl = $tempPhoto->temporaryUrl(); } catch (\Throwable $e) { $previewUrl = null; }
                } elseif ($existingPhoto) {
                    $previewUrl = \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($existingPhoto);
                }
            @endphp
            <div wire:key="story-row-{{ $i }}" class="glass-sm rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase tracking-widest text-emerald-400/70 font-bold">
                        Momen #{{ $i + 1 }}
                    </span>
                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="moveStoryUp({{ $i }})"
                                @disabled($i === 0)
                                class="p-1 text-white/40 hover:text-white {{ $i === 0 ? 'opacity-30 cursor-not-allowed' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <button type="button" wire:click="moveStoryDown({{ $i }})"
                                @disabled($i === count($stories->rows) - 1)
                                class="p-1 text-white/40 hover:text-white {{ $i === count($stories->rows) - 1 ? 'opacity-30 cursor-not-allowed' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <button type="button" wire:click="removeStoryRow({{ $i }})"
                                wire:confirm="Hapus momen ini?"
                                class="p-1 text-red-300/60 hover:text-red-300 ml-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-3">
                        <label class="block text-[11px] text-white/50 mb-1">Tahun *</label>
                        <input wire:model="stories.rows.{{ $i }}.year" type="text"
                               placeholder="2020"
                               class="admin-input w-full px-2 py-1.5 text-xs font-mono @error("stories.rows.$i.year") border-red-400/50 @enderror">
                        @error("stories.rows.$i.year") <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-9">
                        <label class="block text-[11px] text-white/50 mb-1">Judul Momen *</label>
                        <input wire:model="stories.rows.{{ $i }}.title" type="text"
                               placeholder="Pertama bertemu"
                               class="admin-input w-full px-2 py-1.5 text-xs @error("stories.rows.$i.title") border-red-400/50 @enderror">
                        @error("stories.rows.$i.title") <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-12">
                        <label class="block text-[11px] text-white/50 mb-1">Deskripsi</label>
                        <textarea wire:model="stories.rows.{{ $i }}.description" rows="3"
                                  placeholder="Cerita singkat tentang momen ini..."
                                  class="admin-input w-full px-2 py-1.5 text-xs resize-y @error("stories.rows.$i.description") border-red-400/50 @enderror"></textarea>
                        @error("stories.rows.$i.description") <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-12">
                        <label class="block text-[11px] text-white/50 mb-1.5">Foto (opsional)</label>
                        <div class="flex items-start gap-3">
                            @if ($previewUrl)
                                <div class="relative w-24 h-24 flex-shrink-0">
                                    <img src="{{ $previewUrl }}" alt="Story #{{ $i + 1 }}"
                                         class="w-full h-full object-cover rounded-lg ring-1 ring-white/10">
                                </div>
                            @endif
                            <div class="flex-1 space-y-1.5">
                                <input id="storyPhoto-{{ $i }}" type="file"
                                       wire:model="storyPhotos.{{ $i }}"
                                       accept="image/jpeg,image/png,image/webp"
                                       class="text-xs text-white/60 w-full">
                                <div wire:loading wire:target="storyPhotos.{{ $i }}" class="text-[11px] text-emerald-300/80">
                                    Mengunggah...
                                </div>
                                @if ($previewUrl)
                                    <button type="button"
                                            wire:click="removeStoryPhoto({{ $i }})"
                                            wire:confirm="Hapus foto momen ini?"
                                            class="text-[11px] text-red-300/70 hover:text-red-300 inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Hapus foto
                                    </button>
                                @endif
                                @error("storyPhotos.$i") <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between border-t border-white/8 pt-4">
        <button type="button" wire:click="addStoryRow" class="btn-ghost text-xs flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Momen
        </button>
        <div class="flex items-center gap-2">
            <button wire:click="discardTab('stories')"
                    wire:confirm="Buang semua perubahan di tab ini?"
                    class="btn-ghost text-xs">Batalkan</button>
            <button wire:click="saveStories" wire:loading.attr="disabled" wire:target="saveStories"
                    class="btn-primary text-xs">
                <span wire:loading.remove wire:target="saveStories">Simpan Cerita</span>
                <span wire:loading wire:target="saveStories">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
