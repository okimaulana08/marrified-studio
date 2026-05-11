<div x-data="{
    tab: sessionStorage.getItem('invitationEditorTab') || 'basic',
    hasDirty: false,
}"
     x-init="$watch('tab', v => sessionStorage.setItem('invitationEditorTab', v))"
     x-on:invitation-saved.window="
        hasDirty = false;
        const iframe = $refs.previewFrame;
        if (iframe) {
            const baseSrc = iframe.dataset.baseSrc;
            iframe.src = baseSrc + '?v=' + Date.now();
        }
     "
     x-on:jump-to-tab.window="tab = $event.detail">

    {{-- Header --}}
    <div class="flex items-end justify-between mb-6">
        <div class="flex-1 min-w-0">
            @if ($isNew)
                <p class="text-xs uppercase tracking-[0.25em] text-emerald-400/70 font-semibold mb-2 flex items-center gap-2">
                    <span class="inline-block w-6 h-px bg-emerald-400/50"></span>
                    New Invitation
                </p>
                <h1 class="font-display text-4xl font-bold tracking-display text-gradient leading-tight">
                    Buat Invitation Baru
                </h1>
                <p class="text-sm text-white/40 mt-2">
                    Pilih tema, slug, dan jenis religi. Setelah simpan kamu bisa lengkapi data lain.
                </p>
            @else
                <p class="text-xs uppercase tracking-[0.25em] text-emerald-400/70 font-semibold mb-2 flex items-center gap-2">
                    <span class="inline-block w-6 h-px bg-white"></span>
                    Editing Invitation {{ $isAdmin ? '· admin' : '' }}
                </p>
                <h1 class="font-display text-4xl font-bold tracking-display text-gradient leading-tight truncate">
                    {{ $slug }}
                </h1>
                <p class="text-sm font-mono text-emerald-400/60 mt-1.5">{{ $basic->themeSlug }}</p>
            @endif
        </div>

        <div class="flex items-center gap-3 flex-shrink-0">
            @unless ($isNew)
                @include('livewire.invitations.partials.completion-gauge')
            @endunless
            @if (!$isNew)
                <a href="{{ route('invitations.preview', $slug) }}" target="_blank"
                   class="btn-ghost flex items-center gap-1.5 text-xs">
                    Full Preview ↗
                </a>
            @endif
            <a href="{{ $isAdmin ? route('admin.invitations.index') : route('dashboard') }}"
               class="btn-ghost text-xs">
                ← Kembali
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if ($flashMessage)
        <div wire:key="flash-{{ $flashType }}-{{ md5($flashMessage) }}"
             x-data="{ show: true }" x-show="show" x-cloak x-transition
             x-init="setTimeout(() => show = false, 4000)"
             class="mb-4 px-4 py-3 rounded-xl text-sm font-medium glass-strong
                    {{ $flashType === 'error' ? 'border-red-400/30 text-red-300' : ($flashType === 'info' ? 'border-blue-400/30 text-blue-300' : 'border-emerald-400/30 text-emerald-300') }}">
            {{ $flashMessage }}
        </div>
    @endif

    @php
        // Tabs grouped by workflow phase. Sidebar renders one section per group.
        $tabGroups = [
            'Foundation' => [
                'basic'     => ['label' => 'Info',      'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'enabled' => true],
                'sections'  => ['label' => 'Section',   'icon' => 'M4 6h16M4 12h16M4 18h7', 'enabled' => ! $isNew],
                'music'     => ['label' => 'Music',     'icon' => 'M9 19V6l12-3v13M9 19c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3zm12-3c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3z', 'enabled' => ! $isNew],
            ],
            'Konten' => [
                'couple'    => ['label' => 'Couple',    'icon' => 'M4.318 6.318a4.5 4.5 0 010 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'enabled' => ! $isNew],
                'religious' => ['label' => 'Religi',    'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'enabled' => ! $isNew],
                'stories'   => ['label' => 'Cerita',    'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z', 'enabled' => ! $isNew],
                'events'    => ['label' => 'Events',    'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'enabled' => ! $isNew],
                'countdown' => ['label' => 'Countdown', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'enabled' => ! $isNew],
                'gallery'   => ['label' => 'Gallery',   'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'enabled' => ! $isNew],
                'gift'      => ['label' => 'Gift',      'icon' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7', 'enabled' => ! $isNew],
                'thanks'    => ['label' => 'Penutup',   'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'enabled' => ! $isNew],
            ],
            'Distribusi' => [
                'guests'      => ['label' => 'Tamu',       'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'enabled' => ! $isNew],
                'guestbook'   => ['label' => 'Buku Tamu',  'icon' => 'M3 5a2 2 0 012-2h4.586a1 1 0 01.707.293l1.414 1.414A1 1 0 0012.414 5H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V5z', 'enabled' => ! $isNew],
                'analytics'   => ['label' => 'Analytics',  'icon' => 'M9 19V6l12-3v13M9 19c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3zm12-3c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3z', 'enabled' => ! $isNew],
            ],
        ];
    @endphp

    <div class="flex gap-5 h-[calc(100vh-180px)]">

        {{-- Left: form panel — 2/3 of available width when editing existing invitation,
             so users get more breathing room for inputs while keeping a usable preview pane. --}}
        <div class="w-full {{ !$isNew ? 'lg:basis-2/3 lg:flex-grow lg:max-w-none' : '' }} flex flex-col gap-4 min-w-0">

          {{-- Sub-row: vertical sidebar (lg+) | horizontal nav (mobile) + content. Flex-1 so it fills available height; footer below stays at the bottom. --}}
          <div class="flex flex-col lg:flex-row gap-4 flex-1 min-h-0">

            {{-- ───────── Vertical sidebar nav (lg+) ───────── --}}
            <aside class="hidden lg:flex flex-col w-44 xl:w-48 flex-shrink-0 glass rounded-2xl p-2 overflow-y-auto">
                @foreach ($tabGroups as $groupLabel => $items)
                    <p class="px-2.5 pt-2 pb-1.5 text-[10px] uppercase tracking-widest text-white/35 font-bold">{{ $groupLabel }}</p>
                    <div class="space-y-0.5 mb-2">
                        @foreach ($items as $key => $meta)
                            <button type="button"
                                    x-on:click="tab = '{{ $key }}'"
                                    :class="tab === '{{ $key }}' ? 'text-white' : 'text-white/55 hover:text-white/85'"
                                    x-bind:style="tab === '{{ $key }}'
                                        ? 'background: linear-gradient(135deg, rgba(232,62,140,0.20) 0%, rgba(255,122,133,0.12) 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.08); border-color: rgba(232,62,140,0.40);'
                                        : 'background: transparent; border-color: transparent;'"
                                    @disabled(! $meta['enabled'])
                                    class="relative w-full flex items-center gap-2 px-2.5 py-1.5 text-xs font-medium rounded-lg transition-all border {{ $meta['enabled'] ? '' : 'opacity-35 cursor-not-allowed' }}">
                                {{-- Active bar (left edge) --}}
                                <span x-show="tab === '{{ $key }}'" x-cloak
                                      class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 rounded-r"
                                      style="background: linear-gradient(180deg, #e83e8c, #ff7a85);"></span>
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}"/>
                                </svg>
                                <span class="truncate flex-1 text-left">{{ $meta['label'] }}</span>
                                @if (! $meta['enabled'])
                                    <span class="text-[8px] px-1 py-px rounded bg-white/10 text-white/40 uppercase tracking-wider font-mono">soon</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </aside>

            {{-- ───────── Horizontal scrollable nav (mobile/tablet only) ───────── --}}
            <div class="lg:hidden glass rounded-2xl p-1.5 flex gap-0.5 overflow-x-auto flex-shrink-0">
                @foreach ($tabGroups as $groupLabel => $items)
                    @foreach ($items as $key => $meta)
                        <button type="button"
                                x-on:click="tab = '{{ $key }}'"
                                :class="tab === '{{ $key }}' ? 'text-white shadow-inner' : 'text-white/40 hover:text-white/70'"
                                x-bind:style="tab === '{{ $key }}'
                                    ? 'background: linear-gradient(135deg, rgba(232,62,140,0.25) 0%, rgba(255,122,133,0.18) 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.1), 0 0 16px -4px rgba(232,62,140,0.4); border: 1px solid rgba(232,62,140,0.4);'
                                    : 'background: transparent; border: 1px solid transparent;'"
                                @disabled(! $meta['enabled'])
                                class="relative flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all {{ $meta['enabled'] ? '' : 'opacity-30 cursor-not-allowed' }}">
                            {{ $meta['label'] }}
                        </button>
                    @endforeach
                @endforeach
            </div>

            {{-- Tab panels — flex-1 di sebelah kanan sidebar --}}
            <div class="flex-1 glass rounded-2xl p-6 overflow-y-auto relative min-w-0">
                <div x-show="tab === 'basic'" x-cloak class="fade-up">
                    @include('livewire.invitations.partials.tab-basic')

                    @unless ($isNew)
                        <div class="border-t border-white/8 pt-4 mt-6 flex justify-end gap-2">
                            <button wire:click="discardTab('basic')"
                                    wire:confirm="Buang semua perubahan di tab ini?"
                                    class="btn-ghost text-xs">Batalkan</button>
                            <button wire:click="save" wire:loading.attr="disabled" wire:target="save" class="btn-primary text-xs">
                                <span wire:loading.remove wire:target="save">Simpan Info</span>
                                <span wire:loading wire:target="save">Menyimpan...</span>
                            </button>
                        </div>
                    @endunless
                </div>

                @unless ($isNew)
                    <div x-show="tab === 'couple'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-couple')
                    </div>
                    <div x-show="tab === 'stories'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-story')
                    </div>
                    <div x-show="tab === 'events'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-events')
                    </div>
                    <div x-show="tab === 'countdown'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-countdown')
                    </div>
                    <div x-show="tab === 'religious'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-religious')
                    </div>
                    <div x-show="tab === 'sections'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-sections')
                    </div>
                    <div x-show="tab === 'gallery'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-gallery')
                    </div>
                    <div x-show="tab === 'music'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-music')
                    </div>
                    <div x-show="tab === 'thanks'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-thanks')
                    </div>
                    <div x-show="tab === 'gift'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-gift')
                    </div>
                    <div x-show="tab === 'guests'" x-cloak class="fade-up">
                        <livewire:invitations.guests-tab
                            :invitation-id="$invitationId"
                            :is-admin="$isAdmin"
                            :key="'guests-tab-'.$invitationId" />
                    </div>
                    <div x-show="tab === 'guestbook'" x-cloak class="fade-up">
                        <livewire:invitations.guestbook-moderation
                            :invitation-id="$invitationId"
                            :is-admin="$isAdmin"
                            :key="'guestbook-moderation-'.$invitationId" />
                    </div>
                    <div x-show="tab === 'analytics'" x-cloak class="fade-up">
                        <livewire:invitations.analytics-tab
                            :invitation-id="$invitationId"
                            :is-admin="$isAdmin"
                            :key="'analytics-tab-'.$invitationId" />
                    </div>
                @endunless
            </div>

          </div> {{-- /sub-row (sidebar + content) --}}

            {{-- Footer for create-mode (sticky save) --}}
            @if ($isNew)
                <div class="glass rounded-2xl px-5 py-3 flex items-center gap-3">
                    <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                            class="btn-primary">
                        <span wire:loading.remove wire:target="save" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Buat Invitation
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Right: live preview iframe (only when editing existing) --}}
        @if (!$isNew)
            <div class="hidden lg:flex flex-col lg:basis-1/3 lg:flex-grow-0 min-w-0">
                <div class="glass rounded-2xl px-4 py-2.5 mb-3 flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="relative flex w-1.5 h-1.5">
                            <span class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping" style="background-color: #10b981 !important;"></span>
                            <span class="relative inline-flex rounded-full w-1.5 h-1.5" style="background-color: #10b981 !important;"></span>
                        </span>
                        <span class="text-[10px] uppercase tracking-widest font-bold" style="color: rgba(52,211,153,0.85) !important;">Live</span>
                    </div>
                </div>
                <div class="flex-1 flex justify-center items-start overflow-auto glass rounded-2xl p-4 relative">
                    <div class="mx-auto relative z-10"
                         style="height: min(100%, 747px); aspect-ratio: 9 / 16;">
                        <iframe x-ref="previewFrame"
                            wire:ignore
                            data-base-src="{{ route('invitations.preview', $slug) }}"
                            src="{{ route('invitations.preview', $slug) }}?v={{ $previewKey }}"
                            class="w-full h-full rounded-2xl bg-white shadow-2xl"
                            style="box-shadow: 0 24px 60px -12px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.1);"
                            loading="lazy"
                            title="Preview: {{ $slug }}">
                        </iframe>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- Editor-wide QR preview modal. Any "show-qr" event from any tab opens this. --}}
    <div x-data="{ open: false, name: '', url: '' }"
         x-on:show-qr.window="open = true; name = $event.detail.name; url = $event.detail.url"
         x-on:keydown.escape.window="open = false">
        <div x-show="open" x-cloak x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-on:click.self="open = false">
            <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
            <div x-show="open" x-transition
                 class="relative z-10 w-full max-w-sm glass-strong rounded-2xl border border-white/10 shadow-2xl p-6 text-center"
                 style="background: rgba(15, 17, 23, 0.95);">
                <p class="text-[10px] uppercase tracking-widest text-emerald-300/70 font-bold mb-1">QR Code</p>
                <h3 class="font-display text-lg font-semibold text-white mb-3 truncate" x-text="name"></h3>
                <div class="bg-white rounded-xl p-3 mx-auto inline-block">
                    <img :src="url" alt="QR Code" class="block w-56 h-56" loading="eager">
                </div>
                <p class="text-[11px] text-white/45 mt-3">Scan dengan kamera HP → langsung buka undangan</p>
                <div class="flex justify-center gap-2 mt-4">
                    <a :href="url" target="_blank" :download="'qr-' + name + '.png'"
                       class="btn-primary text-xs">Download PNG</a>
                    <button x-on:click="open = false" class="btn-ghost text-xs">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
