<div class="space-y-4">
    <div>
        <h2 class="text-sm font-semibold text-white/80 uppercase tracking-widest mb-1">Assets</h2>
        <p class="text-xs text-white/30">
            File disimpan di <code class="text-[10px] bg-white/8 px-1.5 py-0.5 rounded font-mono text-white/40">resources/themes/{{ $slug }}/assets/</code>
        </p>
    </div>

    @if ($isNew || !$slug)
        <div class="p-4 bg-amber-400/10 border border-amber-400/20 rounded-xl text-sm text-amber-300/80">
            Simpan tema terlebih dahulu sebelum mengupload asset.
        </div>
    @else
        <livewire:admin.asset-library :slug="$slug" />
    @endif
</div>
