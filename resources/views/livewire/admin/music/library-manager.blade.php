<div>
    {{-- Flash --}}
    @if ($flashMessage)
        <div wire:key="music-flash-{{ md5($flashMessage) }}"
             x-data="{ show: true }" x-show="show" x-cloak x-transition
             x-init="setTimeout(() => show = false, 4000)"
             class="mb-4 px-4 py-3 rounded-xl text-sm font-medium glass-strong
                    {{ $flashType === 'error' ? 'border-red-400/30 text-red-300' : 'border-emerald-400/30 text-emerald-300' }}">
            {{ $flashMessage }}
        </div>
    @endif

    {{-- Upload form --}}
    <div class="glass rounded-2xl p-5 mb-5">
        <h2 class="font-display text-lg font-semibold text-white mb-4">Upload Track Baru</h2>
        <div class="grid grid-cols-12 gap-3">
            <div class="col-span-12 md:col-span-5">
                <label class="block text-[11px] text-white/50 mb-1.5 uppercase tracking-wider">Judul *</label>
                <input wire:model="title" type="text" placeholder="Wedding March"
                       class="admin-input w-full px-3 py-2 text-sm @error('title') border-red-400/50 @enderror">
                @error('title') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-12 md:col-span-4">
                <label class="block text-[11px] text-white/50 mb-1.5 uppercase tracking-wider">Artist</label>
                <input wire:model="artist" type="text" placeholder="Mendelssohn"
                       class="admin-input w-full px-3 py-2 text-sm">
            </div>
            <div class="col-span-12 md:col-span-3">
                <label class="block text-[11px] text-white/50 mb-1.5 uppercase tracking-wider">File MP3 *</label>
                <label for="musicUpload"
                       class="file-pick-btn flex items-center justify-center gap-2 px-3 py-2 text-xs cursor-pointer">
                    <svg wire:loading.remove wire:target="newFile" class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <svg wire:loading wire:target="newFile" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="newFile" class="truncate">
                        @if ($newFile && method_exists($newFile, 'getClientOriginalName'))
                            {{ $newFile->getClientOriginalName() }}
                        @else
                            Pilih File MP3
                        @endif
                    </span>
                    <span wire:loading wire:target="newFile">Mengunggah...</span>
                </label>
                <input id="musicUpload" type="file" wire:model="newFile" accept="audio/mpeg,.mp3" class="sr-only">
                @error('newFile') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/8">
            <p class="text-[11px] text-white/40">Format: MP3 &middot; maks {{ \App\Services\Music\MusicLibrary::MAX_FILE_KB / 1024 }} MB</p>
            <button wire:click="uploadTrack" wire:loading.attr="disabled" wire:target="uploadTrack,newFile"
                    class="btn-primary text-xs">
                <span wire:loading.remove wire:target="uploadTrack">Upload Track</span>
                <span wire:loading wire:target="uploadTrack">Memproses...</span>
            </button>
        </div>
    </div>

    {{-- Library list --}}
    @if ($tracks->isEmpty())
        <div class="glass rounded-2xl p-12 text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center mb-4"
                 style="background: linear-gradient(135deg, rgba(16,185,129,0.18), rgba(20,184,166,0.12)); border: 1px solid rgba(16,185,129,0.25);">
                <svg class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3zm12-3c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3z"/>
                </svg>
            </div>
            <h3 class="font-display text-lg font-semibold text-white mb-1">Library masih kosong</h3>
            <p class="text-sm text-white/50 mb-2 max-w-md mx-auto">
                Upload MP3 di form atas. Track yang ada akan tampil di sini, couple bisa pilih saat edit invitation.
            </p>
        </div>
    @else
        <div class="glass rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-white/5 border-b border-white/8">
                    <tr class="text-left text-[11px] uppercase tracking-widest text-white/50">
                        <th class="px-4 py-2.5 font-semibold">Title</th>
                        <th class="px-4 py-2.5 font-semibold">Artist</th>
                        <th class="px-4 py-2.5 font-semibold">Preview</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Dipakai</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tracks as $track)
                        <tr wire:key="track-{{ $track->id }}" class="border-t border-white/5 hover:bg-white/[0.02]">
                            <td class="px-4 py-3 text-white/85">{{ $track->title }}</td>
                            <td class="px-4 py-3 text-white/60">{{ $track->artist ?: '—' }}</td>
                            <td class="px-4 py-3">
                                <audio controls preload="none"
                                       src="{{ \Illuminate\Support\Facades\Storage::disk('music_assets')->url($track->file_path) }}"
                                       class="h-8 max-w-[280px]"></audio>
                            </td>
                            <td class="px-4 py-3 text-right text-white/60 font-mono text-xs">
                                {{ $track->invitations_count }} invitation
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button wire:click="confirmDelete({{ $track->id }})"
                                        class="btn-ghost text-xs text-red-300/80 hover:text-red-300">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-data x-on:keydown.escape.window="$wire.closeDeleteModal()">
            <div class="glass rounded-2xl p-6 max-w-md w-full">
                <h3 class="font-display text-xl font-semibold text-white mb-2">Hapus Track?</h3>
                <p class="text-sm text-white/60 mb-1">
                    Track: <span class="text-emerald-300">{{ $deleteTargetTitle }}</span>
                </p>
                <p class="text-sm text-amber-200/80 mb-5">
                    File MP3 akan dihapus dari disk. Invitation yang memakai track ini akan otomatis dilepas
                    (musik mereka jadi kosong) tapi invitation-nya tetap aman.
                </p>
                <div class="flex justify-end gap-2">
                    <button wire:click="closeDeleteModal" class="btn-ghost text-xs">Batal</button>
                    <button wire:click="deleteTrack" wire:loading.attr="disabled" wire:target="deleteTrack"
                            class="btn-primary text-xs"
                            style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        Ya, hapus
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
