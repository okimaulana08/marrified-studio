@php
    // Selectors grouped by section type. Each entry: selector => description.
    // Searchable + scrollable list di sidebar.
    $selectorGroups = [
        'Base (semua page)' => [
            '.section' => 'Pembungkus tiap halaman/section',
            '.section-inner' => 'Container dalam tiap section',
            '.section-inner--centered' => 'Variant container yang ter-center horizontal',
            '.section-title' => 'Judul section (Acara, Cerita, Galeri, dst)',
            '.section-lede' => 'Deskripsi singkat di bawah judul',
        ],
        'Cover' => [
            '.section--cover' => 'Container halaman cover',
            '.section--cover-minimal' => 'Cover variant "minimal"',
            '.section--cover-portal' => 'Cover variant "portal" (bingkai arch)',
            '.cover-inner' => 'Kotak konten cover',
            '.cover-inner--portal' => 'Kotak konten cover portal',
            '.cover-title' => 'Judul utama (nama pasangan)',
            '.cover-title--script' => 'Variant judul gaya script/kursif',
            '.cover-eyebrow' => 'Teks kecil di atas judul (mis. "The Wedding of")',
            '.cover-eyebrow--date' => 'Eyebrow khusus berisi tanggal',
            '.cover-date' => 'Tampilan tanggal di cover',
            '.cover-open-btn' => 'Tombol "Open Invitation"',
            '.cover-open-btn--minimal' => 'Tombol open variant minimal',
            '.cover-open-btn--portal' => 'Tombol open variant portal',
        ],
        'Sambutan Tamu' => [
            '.guest-greeting' => 'Kotak sambutan untuk nama tamu',
            '.guest-greeting--minimal' => 'Sambutan tamu variant minimal',
            '.guest-honorific' => 'Sapaan kehormatan (mis. "Kepada Yth.")',
            '.guest-name' => 'Nama tamu',
            '.guest-relation' => 'Relasi tamu (mis. "Sahabat")',
        ],
        'Ayat / Quotes' => [
            '.section--quotes' => 'Container halaman ayat/quote',
            '.ayat-arabic' => 'Teks ayat Arab/asli',
            '.ayat-translation' => 'Terjemahan ayat',
            '.ayat-source' => 'Sumber kutipan (mis. "QS. Ar-Rum: 21")',
        ],
        'Couple / Pengantin' => [
            '.couple-grid' => 'Grid yang menampung mempelai pria & wanita',
            '.couple-person' => 'Kartu masing-masing mempelai',
            '.couple-and' => 'Pembatas "&" antara dua mempelai',
            '.couple-photo' => 'Foto mempelai',
            '.couple-photo-placeholder' => 'Placeholder saat foto kosong',
            '.couple-name' => 'Nama panggilan mempelai',
            '.couple-fullname' => 'Nama lengkap mempelai',
            '.couple-parents' => 'Nama orang tua mempelai',
            '.couple-ig' => 'Handle Instagram mempelai',
        ],
        'Cerita Cinta (Story)' => [
            '.section--story' => 'Container halaman cerita',
            '.story-timeline' => 'Layout timeline vertikal',
            '.story-cards' => 'Layout cards bertumpuk',
            '.story-entry' => 'Satu entry cerita di timeline',
            '.story-entry--left' => 'Entry yang ditempatkan di kiri',
            '.story-entry--right' => 'Entry yang ditempatkan di kanan',
            '.story-entry-card' => 'Kartu konten dalam entry',
            '.story-entry-dot' => 'Titik di tengah timeline',
            '.story-entry-year' => 'Badge tahun',
            '.story-entry-title' => 'Judul entry',
            '.story-entry-text' => 'Deskripsi entry',
            '.story-entry-photo' => 'Foto entry (opsional)',
            '.story-card' => 'Kartu di layout cards',
            '.story-card-year' => 'Tahun di kartu',
            '.story-card-title' => 'Judul di kartu',
            '.story-card-text' => 'Deskripsi di kartu',
            '.story-card-photo' => 'Foto di kartu',
        ],
        'Acara (Event)' => [
            '.event-list' => 'List semua acara',
            '.event-card' => 'Kartu satu acara (akad / resepsi)',
            '.event-type' => 'Label tipe acara',
            '.event-name' => 'Nama acara',
            '.event-date' => 'Tanggal acara',
            '.event-venue' => 'Nama tempat acara',
            '.event-address' => 'Alamat tempat acara',
            '.event-maps-btn' => 'Tombol "Lihat di Maps"',
        ],
        'Countdown (Hitung Mundur)' => [
            '.section--countdown' => 'Container halaman countdown',
            '.section--countdown-digital' => 'Variant 4-kotak digital',
            '.section--countdown-minimal' => 'Variant satu baris minimal',
            '.countdown-cell' => 'Tiap kotak (Hari/Jam/Menit/Detik)',
            '.countdown-value' => 'Angka di tiap kotak',
            '.countdown-label' => 'Label di bawah angka',
            '.countdown-line' => 'Baris angka variant minimal',
            '.countdown-line-num' => 'Angka di variant minimal',
            '.countdown-line-unit' => 'Satuan (hari/jam) di variant minimal',
            '.countdown-eyebrow' => 'Teks "menuju [nama acara]"',
            '.countdown-message' => 'Pesan tambahan di bawah countdown',
        ],
        'Galeri Foto' => [
            '.gallery-grid' => 'Layout grid foto',
            '.gallery-grid img' => 'Tiap foto di grid',
            '.gallery-item' => 'Wrapper tiap item foto',
            '.gallery-staggered' => 'Layout masonry Instagram',
            '.gallery-staggered-item' => 'Tiap foto di masonry',
            '.gl-figure' => 'Kontainer foto saat dibuka fullscreen',
            '.gl-image' => 'Foto di mode fullscreen (lightbox)',
            '.gl-counter' => 'Counter "1 / 12" di lightbox',
        ],
        'Hadiah / Tanda Kasih (Gift)' => [
            '.section--gift-inline' => 'Variant kartu inline (tanpa modal)',
            '.gift-card' => 'Kartu rekening/e-wallet',
            '.gift-card-icon' => 'Ikon di kartu hadiah',
            '.gift-card-body' => 'Isi kartu hadiah',
            '.gift-card-bank' => 'Nama bank / e-wallet',
            '.gift-card-number' => 'Nomor rekening',
            '.gift-card-name' => 'Nama pemilik rekening',
            '.gift-card-copy' => 'Tombol copy nomor',
            '.gift-account' => 'Rekening (variant modal)',
            '.gift-account-bank' => 'Bank di variant modal',
            '.gift-account-number' => 'Nomor di variant modal',
            '.gift-account-name' => 'Pemilik di variant modal',
            '.gift-cta' => 'Tombol pembuka modal hadiah',
            '.gift-modal' => 'Popup modal hadiah',
            '.gift-modal-card' => 'Kartu di dalam modal',
            '.gift-modal-title' => 'Judul modal hadiah',
            '.gift-modal-close' => 'Tombol close modal',
        ],
        'RSVP / Konfirmasi' => [
            '.section--rsvp-card' => 'Variant RSVP dalam kartu mengambang',
            '.rsvp-card-wrapper' => 'Kartu pembungkus form RSVP',
            '.rsvp-card-icon' => 'Ikon amplop di atas kartu',
            '.rsvp-card-body' => 'Isi kartu RSVP',
            '.rsvp-form-wrap' => 'Pembungkus form input',
            '.rsvp-success' => 'Pesan sukses setelah submit',
            '.form-field' => 'Tiap field form (label + input)',
            '.form-label' => 'Label form',
            '.form-error' => 'Pesan error validasi',
            '.form-submit' => 'Tombol submit form',
            '.form-radio-group' => 'Grup radio (mis. Hadir / Tidak Hadir)',
        ],
        'Buku Tamu (Guestbook)' => [
            '.section--guestbook-wall' => 'Variant tampilan dinding (sticky notes)',
            '.guestbook-list' => 'List ucapan/doa',
            '.guestbook-message' => 'Tiap kartu ucapan',
            '.guestbook-name' => 'Nama pengirim ucapan',
            '.guestbook-text' => 'Isi ucapan',
            '.guestbook-time' => 'Waktu posting (mis. "5 menit lalu")',
            '.guestbook-note' => 'Tiap sticky note (variant wall)',
            '.guestbook-note-text' => 'Teks di sticky note',
            '.guestbook-note-name' => 'Nama di sticky note',
            '.guestbook-note-time' => 'Waktu di sticky note',
            '.guestbook-empty' => 'Pesan saat belum ada ucapan',
        ],
        'Halaman Terima Kasih' => [
            '.section--thanks' => 'Container halaman thanks',
            '.section--thanks-photo' => 'Variant dengan foto bulat di atas',
            '.section--thanks-elegant' => 'Variant elegant (foto full-bleed)',
            '.thanks-message' => 'Pesan terima kasih',
            '.thanks-couple' => 'Nama pasangan (signature besar)',
            '.thanks-signature' => 'Teks "Kami yang berbahagia,"',
            '.thanks-divider' => 'Garis pembatas dengan ornamen',
            '.thanks-photo' => 'Foto pasangan di halaman thanks',
        ],
        'Navigasi / Deck UI' => [
            '.invitation-deck' => 'Container utama deck undangan',
            '.deck-nav' => 'Tombol navigasi up/down',
            '.deck-nav-next' => 'Tombol nav ke bawah/halaman berikut',
            '.deck-nav-prev' => 'Tombol nav ke atas/halaman sebelum',
            '.deck-pagination' => 'Indikator dots di sisi kanan',
            '.bgm-toggle' => 'Tombol mute musik (pojok kanan bawah)',
            '.render-body' => 'Body undangan (canvas paling luar)',
            '.page-content' => 'Wrapper konten tiap page (scrollable)',
        ],
    ];
    $cssVars = [
        '--p' => 'Warna utama (primary)',
        '--a' => 'Warna aksen',
        '--a2' => 'Warna aksen kedua',
        '--bg' => 'Warna background',
        '--ink' => 'Warna teks default',
        '--muted' => 'Warna teks pudar',
        '--fd' => 'Font display (judul)',
        '--fb' => 'Font body (isi teks)',
        '--fs' => 'Font script (kursif)',
        '--fs-scale' => 'Skala ukuran font global',
    ];
@endphp

<div class="space-y-5">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-emerald-500/15 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
        </div>
        <div>
            <h2 class="font-display text-lg font-semibold text-white tracking-display leading-none">Custom CSS</h2>
            <p class="text-[11px] text-white/40 mt-0.5">Override style apa pun di tema. Ditulis terakhir di &lt;head&gt; jadi prioritas paling tinggi.</p>
        </div>
    </div>

    @php $totalSelectors = array_sum(array_map('count', $selectorGroups)); @endphp

    {{-- Toolbar: open selector modal + variables popover --}}
    <div class="flex flex-wrap items-center gap-2"
         x-data="{ pickerOpen: false, varsOpen: false }"
         x-on:keydown.escape.window="pickerOpen = false; varsOpen = false">
        <button type="button" x-on:click="pickerOpen = true"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg glass-sm border border-emerald-400/25 hover:border-emerald-400/50 text-emerald-200/90 hover:text-emerald-100 text-xs transition-all">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h10M4 18h7"/>
            </svg>
            Cari Selector
            <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-[10px] font-mono">{{ $totalSelectors }}</span>
        </button>

        <button type="button" x-on:click="varsOpen = true"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg glass-sm border border-white/10 hover:border-white/25 text-white/70 hover:text-white text-xs transition-all">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h10M7 16h6"/>
            </svg>
            CSS Variables
            <span class="px-1.5 py-0.5 rounded bg-white/10 text-[10px] font-mono">{{ count($cssVars) }}</span>
        </button>

        {{-- CSS Variables Modal — same teleported pattern as the selector modal. --}}
        <template x-teleport="body">
            <div x-show="varsOpen" x-cloak
                 x-transition.opacity
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-8"
                 x-on:keydown.escape.window="varsOpen = false">
                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" x-on:click="varsOpen = false"></div>

                <div x-show="varsOpen" x-transition
                     class="relative z-10 w-full max-w-xl max-h-[88vh] glass-strong rounded-2xl border border-white/10 shadow-2xl overflow-hidden flex flex-col"
                     style="background: rgba(15, 17, 23, 0.95);">
                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-white/8 flex-shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h10M7 16h6"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white">CSS Variables</h3>
                                <p class="text-[11px] text-white/45">{{ count($cssVars) }} variabel tema — klik untuk insert ke editor</p>
                            </div>
                        </div>
                        <button type="button" x-on:click="varsOpen = false"
                                class="w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center text-white/55 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- List --}}
                    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-px">
                        @foreach ($cssVars as $name => $label)
                            <button type="button"
                                    x-on:click="$dispatch('css-chip', 'var({{ $name }})'); varsOpen = false"
                                    class="w-full text-left px-3 py-2 rounded-lg hover:bg-emerald-500/10 transition-colors group flex items-baseline gap-4">
                                <code class="text-[12px] font-mono text-emerald-300 group-hover:text-emerald-200 whitespace-nowrap min-w-[120px] flex-shrink-0">var({{ $name }})</code>
                                <span class="text-[12px] text-white/55 group-hover:text-white/80 leading-snug">{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Footer --}}
                    <div class="px-5 py-2.5 border-t border-white/8 text-[11px] text-white/40 flex items-center justify-between flex-shrink-0">
                        <span>Tekan <kbd class="px-1.5 py-0.5 rounded bg-white/10 font-mono text-[10px]">Esc</kbd> untuk tutup</span>
                        <span class="text-white/30">Pakai sebagai <code class="font-mono">value</code>, bukan selector</span>
                    </div>
                </div>
            </div>
        </template>

        <span class="text-[10px] text-white/35">
            Ketik <kbd class="px-1 py-px rounded bg-white/10 font-mono">.</kbd>
            atau <kbd class="px-1 py-px rounded bg-white/10 font-mono">--</kbd>
            untuk autocomplete inline
        </span>

        {{-- Selector Modal — teleported to body so it escapes any clipping parent. --}}
        <template x-teleport="body">
            <div x-show="pickerOpen" x-cloak
                 x-transition.opacity
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-8"
                 x-on:keydown.escape.window="pickerOpen = false">
                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" x-on:click="pickerOpen = false"></div>

                <div x-show="pickerOpen" x-transition
                     x-data="{ search: '' }"
                     x-init="$nextTick(() => $refs.searchInput?.focus())"
                     class="relative z-10 w-full max-w-2xl max-h-[88vh] glass-strong rounded-2xl border border-white/10 shadow-2xl overflow-hidden flex flex-col"
                     style="background: rgba(15, 17, 23, 0.95);">
                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-white/8 flex-shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h10M4 18h7"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white">CSS Selector</h3>
                                <p class="text-[11px] text-white/45">{{ $totalSelectors }} selector tersedia — klik untuk insert ke editor</p>
                            </div>
                        </div>
                        <button type="button" x-on:click="pickerOpen = false"
                                class="w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center text-white/55 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="px-5 py-3 border-b border-white/8 flex-shrink-0">
                        <div class="relative">
                            <input x-ref="searchInput" x-model="search" type="text"
                                   placeholder="Cari selector atau deskripsi… (mis. 'judul', 'tombol', 'foto')"
                                   class="w-full px-3 py-2 pl-9 text-sm rounded-lg bg-white/5 border border-white/10 text-white placeholder:text-white/30 focus:outline-none focus:border-emerald-400/50 focus:bg-white/8">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/35" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Grouped list — single column, descriptions wrap freely --}}
                    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5">
                        @foreach ($selectorGroups as $group => $items)
                            @php $entries = collect($items)->map(fn($desc, $sel) => ['sel' => $sel, 'desc' => $desc])->values()->all(); @endphp
                            <div x-data="{ entries: @js($entries) }"
                                 x-show="search === '' || entries.some(e => (e.sel + ' ' + e.desc).toLowerCase().includes(search.toLowerCase()))">
                                <p class="text-[10px] uppercase tracking-widest text-emerald-300/70 font-semibold mb-2 flex items-center gap-2 sticky top-0 py-1"
                                   style="background: rgba(15, 17, 23, 0.95);">
                                    <span class="w-1 h-3 bg-emerald-400/60 rounded-full"></span>
                                    {{ $group }}
                                </p>
                                <div class="space-y-px">
                                    @foreach ($items as $sel => $desc)
                                        <button type="button"
                                                x-on:click="$dispatch('css-chip', '{{ $sel }}'); pickerOpen = false"
                                                x-show="search === '' || ('{{ $sel }} {{ addslashes($desc) }}').toLowerCase().includes(search.toLowerCase())"
                                                class="w-full text-left px-3 py-2 rounded-lg hover:bg-emerald-500/10 transition-colors group flex items-baseline gap-4">
                                            <code class="text-[12px] font-mono text-white/85 group-hover:text-emerald-200 whitespace-nowrap min-w-[180px] flex-shrink-0">{{ $sel }}</code>
                                            <span class="text-[12px] text-white/55 group-hover:text-white/80 leading-snug">{{ $desc }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Footer hint --}}
                    <div class="px-5 py-2.5 border-t border-white/8 text-[11px] text-white/40 flex items-center gap-3 flex-shrink-0">
                        <span>Tekan <kbd class="px-1.5 py-0.5 rounded bg-white/10 font-mono text-[10px]">Esc</kbd> untuk tutup</span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Editor (full width) --}}
    <div x-data="customCssEditor(@js($customCss->customCss))"
         x-on:css-chip.window="insertChip($event.detail)"
         wire:ignore>
        <div x-ref="host" class="rounded-xl overflow-hidden border border-white/8 bg-[#1c1f26]"></div>
        <p class="text-[11px] text-white/35 mt-1.5 flex items-center gap-3">
            <span x-text="ready ? 'Editor siap.' : 'Memuat editor…'"></span>
            <span class="font-mono" x-text="length + '/30000'"></span>
        </p>
    </div>

    {{-- Tips --}}
    <div class="p-2.5 rounded-xl glass-sm border border-amber-400/15 text-[11px] text-amber-200/80 leading-relaxed">
        <strong class="text-amber-300">Tips:</strong>
        CSS di-inject setelah bundle jadi prioritas paling tinggi. Tag <code class="font-mono">@import</code>, <code class="font-mono">&lt;script&gt;</code>, <code class="font-mono">expression()</code>, dan <code class="font-mono">javascript:</code> URLs otomatis dibuang sebelum disimpan.
    </div>

    @error('customCss.customCss')
        <p class="text-xs text-red-400">{{ $message }}</p>
    @enderror

    <p class="text-[11px] text-white/35 italic">
        Pakai tombol <strong class="text-white/55 not-italic">Simpan</strong> di bawah halaman untuk menyimpan perubahan.
    </p>
</div>

@once
<script>
    // Alpine factory for the CodeMirror-backed editor. Lazy-imports the wrapper
    // module so the bundle is only fetched when this tab is opened.
    window.customCssEditor = function (initial) {
        return {
            ready: false,
            cm: null,
            length: (initial || '').length,
            async init() {
                if (typeof window.loadCustomCssEditor !== 'function') {
                    console.error('CodeMirror loader missing — make sure resources/js/app.js is included.');
                    return;
                }
                const mod = await window.loadCustomCssEditor();
                this.cm = mod.mount(this.$refs.host, initial || '', (value) => {
                    this.length = value.length;
                    // Push back to Livewire form binding (no full network roundtrip).
                    this.$wire.set('customCss.customCss', value, false);
                });
                this.ready = true;
            },
            insertChip(text) {
                if (!this.cm) return;
                const snippet = text.startsWith('var(') ? text : `\n${text} {\n  \n}\n`;
                this.cm.insertAtCursor(snippet);
            },
        };
    };
</script>
@endonce
