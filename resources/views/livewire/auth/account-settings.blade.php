<div class="max-w-2xl mx-auto py-8 space-y-6">

    {{-- Header --}}
    <div class="flex items-end justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.25em] text-emerald-400/70 font-semibold mb-2 flex items-center gap-2">
                <span class="inline-block w-6 h-px bg-emerald-400/50"></span>
                Pengaturan Akun
            </p>
            <h1 class="font-display text-3xl font-bold tracking-display text-gradient leading-tight">
                Halo, {{ $user->name }}
            </h1>
            <p class="text-sm text-white/45 mt-1.5">{{ $user->email }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn-ghost text-xs">← Kembali</a>
        </div>
    </div>

    {{-- Flash --}}
    @if ($flashMessage)
        <div wire:key="account-flash-{{ md5($flashMessage) }}"
             x-data="{ show: true }" x-show="show" x-cloak x-transition
             x-init="setTimeout(() => show = false, 4000)"
             class="px-4 py-3 rounded-xl text-sm font-medium glass-strong
                    {{ $flashType === 'error' ? 'border-red-400/30 text-red-300' : 'border-emerald-400/30 text-emerald-300' }}">
            {{ $flashMessage }}
        </div>
    @endif

    {{-- Profile (read-only) --}}
    <div class="glass rounded-2xl p-6 space-y-4">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-display text-base font-semibold text-white tracking-display leading-none">Profil</h2>
                <p class="text-[11px] text-white/40 mt-0.5">Email tidak bisa diubah dari sini — hubungi admin kalau perlu.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-[11px] text-white/50 mb-1 uppercase tracking-wider">Nama</label>
                <input type="text" value="{{ $user->name }}" readonly
                       class="admin-input w-full px-3 py-2 text-sm opacity-70 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-[11px] text-white/50 mb-1 uppercase tracking-wider">Email</label>
                <input type="email" value="{{ $user->email }}" readonly
                       class="admin-input w-full px-3 py-2 text-sm font-mono opacity-70 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-[11px] text-white/50 mb-1 uppercase tracking-wider">Role</label>
                @php $roleLabel = is_object($user->role) ? ucfirst($user->role->value) : ucfirst((string) ($user->role ?? 'user')); @endphp
                <input type="text" value="{{ $roleLabel }}" readonly
                       class="admin-input w-full px-3 py-2 text-sm opacity-70 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-[11px] text-white/50 mb-1 uppercase tracking-wider">Bergabung</label>
                <input type="text" value="{{ $user->created_at?->translatedFormat('d M Y') }}" readonly
                       class="admin-input w-full px-3 py-2 text-sm opacity-70 cursor-not-allowed">
            </div>
        </div>
    </div>

    {{-- Change password --}}
    <div class="glass rounded-2xl p-6 space-y-4">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-400/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-display text-base font-semibold text-white tracking-display leading-none">Ubah Password</h2>
                <p class="text-[11px] text-white/40 mt-0.5">Minimal 8 karakter. Kamu harus memasukkan password sekarang dulu.</p>
            </div>
        </div>

        <form wire:submit.prevent="changePassword" class="space-y-3">
            <div>
                <label class="block text-[11px] text-white/50 mb-1 uppercase tracking-wider">Password Sekarang</label>
                <input wire:model="currentPassword" type="password" autocomplete="current-password"
                       class="admin-input w-full px-3 py-2 text-sm @error('currentPassword') border-red-400/50 @enderror">
                @error('currentPassword') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] text-white/50 mb-1 uppercase tracking-wider">Password Baru</label>
                    <input wire:model="password" type="password" autocomplete="new-password"
                           class="admin-input w-full px-3 py-2 text-sm @error('password') border-red-400/50 @enderror">
                    @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] text-white/50 mb-1 uppercase tracking-wider">Konfirmasi Password</label>
                    <input wire:model="passwordConfirmation" type="password" autocomplete="new-password"
                           class="admin-input w-full px-3 py-2 text-sm @error('passwordConfirmation') border-red-400/50 @enderror">
                    @error('passwordConfirmation') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="changePassword"
                        class="btn-primary text-xs">
                    <span wire:loading.remove wire:target="changePassword">Simpan Password Baru</span>
                    <span wire:loading wire:target="changePassword">Menyimpan…</span>
                </button>
            </div>
        </form>
    </div>

    {{-- 2FA (placeholder) --}}
    <div class="glass rounded-2xl p-6 opacity-50">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-white/8 border border-white/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-white/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="font-display text-base font-semibold text-white/70 tracking-display leading-none">2-Factor Authentication</h2>
                <p class="text-[11px] text-white/40 mt-0.5">Akan tersedia di update mendatang.</p>
            </div>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-white/10 text-white/50 font-mono uppercase tracking-wider">soon</span>
        </div>
    </div>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}" class="flex justify-center">
        @csrf
        <button type="submit" class="btn-ghost text-xs">Keluar dari Sesi Ini</button>
    </form>
</div>
