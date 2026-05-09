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
     ">

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
                    <span class="inline-block w-6 h-px bg-emerald-400/50"></span>
                    Editing Invitation {{ $isAdmin ? '· admin' : '' }}
                </p>
                <h1 class="font-display text-4xl font-bold tracking-display text-gradient leading-tight truncate">
                    {{ $slug }}
                </h1>
                <p class="text-sm font-mono text-emerald-400/60 mt-1.5">{{ $basic->themeSlug }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
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

    <div class="flex gap-5 h-[calc(100vh-180px)]">

        {{-- Left: form panel --}}
        <div class="w-full {{ !$isNew ? 'lg:w-[480px] xl:w-[540px] flex-shrink-0' : '' }} flex flex-col gap-4">

            {{-- Tab nav --}}
            <div class="glass rounded-2xl p-1.5 flex gap-0.5 overflow-x-auto">
                @foreach ([
                    'basic'     => ['label' => 'Info',     'enabled' => true],
                    'couple'    => ['label' => 'Couple',   'enabled' => ! $isNew],
                    'events'    => ['label' => 'Events',   'enabled' => ! $isNew],
                    'religious' => ['label' => 'Religi',   'enabled' => ! $isNew],
                    'sections'  => ['label' => 'Section',  'enabled' => ! $isNew],
                    'gift'      => ['label' => 'Gift',     'enabled' => ! $isNew],
                    'guests'    => ['label' => 'Tamu',     'enabled' => ! $isNew],
                ] as $key => $meta)
                    <button type="button"
                            x-on:click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'text-white shadow-inner' : 'text-white/40 hover:text-white/70'"
                            x-bind:style="tab === '{{ $key }}'
                                ? 'background: linear-gradient(135deg, rgba(16,185,129,0.25) 0%, rgba(20,184,166,0.18) 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.1), 0 0 16px -4px rgba(16,185,129,0.4); border: 1px solid rgba(16,185,129,0.4);'
                                : 'background: transparent; border: 1px solid transparent;'"
                            @disabled(! $meta['enabled'])
                            class="relative flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-all {{ $meta['enabled'] ? '' : 'opacity-30 cursor-not-allowed' }}">
                        {{ $meta['label'] }}
                        @if (! $meta['enabled'])
                            <span class="text-[8px] px-1 py-px rounded bg-white/10 text-white/40 uppercase tracking-wider font-mono">soon</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Tab panels --}}
            <div class="flex-1 glass rounded-2xl p-6 overflow-y-auto relative">
                <div x-show="tab === 'basic'" x-cloak class="fade-up">
                    @include('livewire.invitations.partials.tab-basic')

                    @unless ($isNew)
                        <div class="border-t border-white/8 pt-4 mt-6 flex justify-end">
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
                    <div x-show="tab === 'events'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-events')
                    </div>
                    <div x-show="tab === 'religious'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-religious')
                    </div>
                    <div x-show="tab === 'sections'" x-cloak class="fade-up">
                        @include('livewire.invitations.partials.tab-sections')
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
                @endunless
            </div>

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
            <div class="hidden lg:flex flex-col flex-1 min-w-0">
                <div class="glass rounded-2xl px-4 py-2.5 mb-3 flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="relative flex w-1.5 h-1.5">
                            <span class="absolute inline-flex w-full h-full bg-emerald-400 rounded-full opacity-75 animate-ping"></span>
                            <span class="relative inline-flex rounded-full w-1.5 h-1.5 bg-emerald-400"></span>
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-emerald-300/80 font-bold">Live</span>
                    </div>
                </div>
                <div class="flex-1 flex justify-center items-start overflow-auto glass rounded-2xl p-4 relative">
                    <iframe x-ref="previewFrame"
                        wire:ignore
                        data-base-src="{{ route('invitations.preview', $slug) }}"
                        src="{{ route('invitations.preview', $slug) }}?v={{ $previewKey }}"
                        class="w-full max-w-[420px] h-full rounded-2xl bg-white shadow-2xl"
                        style="box-shadow: 0 24px 60px -12px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.1);"
                        loading="lazy"
                        title="Preview: {{ $slug }}">
                    </iframe>
                </div>
            </div>
        @endif

    </div>
</div>
