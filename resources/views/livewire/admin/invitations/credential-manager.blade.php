<div class="max-w-2xl mx-auto space-y-5">
    {{-- Header --}}
    <div class="flex items-end justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-emerald-400/70 font-semibold mb-2 flex items-center gap-2">
                <span class="inline-block w-6 h-px bg-emerald-400/50"></span>
                Couple Credentials
            </p>
            <h1 class="font-display text-3xl font-bold tracking-display text-gradient leading-tight truncate">
                {{ $invitation->slug }}
            </h1>
            @if ($invitation->couple)
                <p class="text-sm text-white/40 mt-1.5">
                    {{ $invitation->couple->bride_name }} &amp; {{ $invitation->couple->groom_name }}
                </p>
            @endif
        </div>
        <a href="{{ route('admin.invitations.index') }}" class="btn-ghost text-xs">← List</a>
    </div>

    {{-- Flash --}}
    @if ($flashMessage)
        <div wire:key="cred-flash-{{ md5($flashMessage) }}"
             x-data="{ show: true }" x-show="show" x-cloak x-transition
             x-init="setTimeout(() => show = false, 6000)"
             class="px-4 py-3 rounded-xl text-sm font-medium glass-strong
                    {{ $flashType === 'error' ? 'border-red-400/30 text-red-300'
                       : ($flashType === 'info' ? 'border-blue-400/30 text-blue-300' : 'border-emerald-400/30 text-emerald-300') }}">
            {{ $flashMessage }}
        </div>
    @endif

    {{-- One-time plaintext display --}}
    @if ($freshPlaintext !== null)
        <div class="glass-strong rounded-2xl p-5 border-emerald-400/40"
             x-data="{
                copied: false,
                copy() {
                    navigator.clipboard.writeText('{{ $linkedUser?->email }}\n{{ $freshPlaintext }}');
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }
             }">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/15 border border-emerald-400/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-emerald-300">Kredensial baru — salin sekarang</p>
                    <p class="text-[11px] text-emerald-200/60 mt-0.5">
                        Password hanya tampil sekali ini. Setelah halaman ini ditutup atau di-refresh, hanya regenerate yang bisa men-set ulang.
                    </p>
                </div>
            </div>

            <div class="space-y-2 mb-4">
                <div class="bg-white/5 border border-white/10 rounded-lg px-3 py-2">
                    <p class="text-[10px] text-white/40 uppercase tracking-wider">Email</p>
                    <p class="font-mono text-sm text-white">{{ $linkedUser?->email }}</p>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-lg px-3 py-2">
                    <p class="text-[10px] text-white/40 uppercase tracking-wider">Password</p>
                    <p class="font-mono text-sm text-white tracking-widest">{{ $freshPlaintext }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" x-on:click="copy()" class="btn-primary text-xs">
                    <span x-show="!copied">Salin Email + Password</span>
                    <span x-show="copied" x-cloak>✓ Tersalin</span>
                </button>
                <a href="{{ $loginUrl }}" target="_blank" class="btn-ghost text-xs">Login URL ↗</a>
                <button type="button" wire:click="dismissPlaintext" class="btn-ghost text-xs ml-auto">
                    Tutup
                </button>
            </div>

            <p class="text-[10px] text-white/40 mt-3">
                Bagikan ke couple via WhatsApp / email. Setelah login, mereka bisa edit invitation di
                <code class="font-mono text-emerald-300/70">/invitations/{{ $invitation->slug }}/edit</code>.
            </p>
        </div>
    @endif

    {{-- Main credential card --}}
    <div class="glass rounded-2xl p-6 sm:p-7">
        @if ($linkedUser === null)
            {{-- State 1: No credentials yet --}}
            <h2 class="font-display text-lg font-semibold text-white mb-2">Belum Ada Kredensial</h2>
            <p class="text-sm text-white/50 mb-5 leading-relaxed">
                Buat akun login untuk couple. Mereka akan login dengan email + password yang di-generate untuk edit invitation.
            </p>

            <div>
                <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">Email Couple *</label>
                <input wire:model="email" type="email" placeholder="couple@example.test"
                       class="admin-input w-full px-3 py-2.5 text-sm @error('email') border-red-400/50 @enderror">
                @error('email') <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p> @enderror
                <p class="text-[11px] text-white/30 mt-2">
                    Email ini akan dipakai couple untuk login. Pastikan unik & valid.
                </p>
            </div>

            <div class="flex justify-end mt-5">
                <button wire:click="issue" wire:loading.attr="disabled" wire:target="issue"
                        class="btn-primary text-xs">
                    <span wire:loading.remove wire:target="issue">Generate Kredensial</span>
                    <span wire:loading wire:target="issue">Memproses...</span>
                </button>
            </div>
        @else
            {{-- State 2: Credentials exist --}}
            <h2 class="font-display text-lg font-semibold text-white mb-2">Akses Couple Aktif</h2>
            <p class="text-sm text-white/50 mb-5 leading-relaxed">
                Kredensial sudah di-issue. Couple bisa login dan edit invitation mereka.
            </p>

            <div class="bg-white/5 border border-white/10 rounded-lg p-5 space-y-2 mb-5">
                <div class="flex items-start gap-3 flex-wrap">
                    <span class="text-[10px] uppercase tracking-widest text-white/40 font-bold w-16 shrink-0 pt-0.5">Email</span>
                    <span class="font-mono text-sm text-white break-all">{{ $linkedUser->email }}</span>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-[10px] uppercase tracking-widest text-white/40 font-bold w-16 shrink-0">Status</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-400/30">
                        Aktif
                    </span>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-[10px] uppercase tracking-widest text-white/40 font-bold w-16 shrink-0">Created</span>
                    <span class="text-xs text-white/60">{{ $linkedUser->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button wire:click="regenerate" wire:loading.attr="disabled" wire:target="regenerate"
                        wire:confirm="Yakin? Password lama akan langsung tidak berlaku."
                        class="btn-primary text-xs">
                    <span wire:loading.remove wire:target="regenerate">Regenerate Password</span>
                    <span wire:loading wire:target="regenerate">Memproses...</span>
                </button>
                <button wire:click="revoke" wire:loading.attr="disabled" wire:target="revoke"
                        wire:confirm="Yakin cabut akses? User akan dihapus dan couple tidak bisa login lagi sampai admin issue ulang."
                        class="btn-ghost text-xs text-red-300/80 hover:text-red-300 ml-auto">
                    Cabut Akses
                </button>
            </div>
        @endif
    </div>

    <div class="text-center">
        <a href="{{ route('invitations.edit', $invitation->slug) }}" class="text-xs text-white/40 hover:text-white">
            ← Buka editor invitation ini
        </a>
    </div>
</div>
