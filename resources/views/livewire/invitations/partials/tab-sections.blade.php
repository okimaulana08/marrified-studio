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
            @endphp
            <div wire:key="section-row-{{ $row['id'] }}"
                 data-id="{{ $row['id'] }}"
                 class="glass-sm rounded-xl px-2.5 py-2.5 grid grid-cols-12 gap-2 items-center
                        {{ $row['enabled'] ? '' : 'opacity-50' }}">
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
                    <p class="text-[10px] text-white/30 font-mono">#{{ $i + 1 }}</p>
                </div>
                <div class="col-span-5">
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
                <div class="col-span-2 flex items-center justify-end gap-1">
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
