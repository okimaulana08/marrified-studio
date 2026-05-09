@php
    /** @var \App\Models\Invitation $invitation */
    $accounts = $invitation->giftAccounts;
@endphp

<section id="section-gift" class="section section--gift" data-section="gift">
    <div class="section-inner section-inner--centered" x-data="{ open: false }">
        <h2 class="section-title">Tanda Kasih</h2>
        <p class="section-lede">Terima kasih telah menambah semangat kegembiraan pernikahan kami dengan kehadiran dan hadiah indah Anda.</p>

        <button type="button" class="gift-cta" @click="open = true">💝 Cashless</button>

        <div class="gift-modal" x-show="open" x-transition x-cloak @keydown.escape.window="open = false">
            <div class="gift-modal-backdrop" @click="open = false"></div>
            <div class="gift-modal-card no-swipe">
                <button type="button" class="gift-modal-close" @click="open = false" aria-label="Tutup">×</button>
                <h3 class="gift-modal-title">Hadiah Pernikahan</h3>

                @forelse ($accounts as $account)
                    <article class="gift-account">
                        <p class="gift-account-bank">{{ $account->bank_name }}</p>
                        <p class="gift-account-number" x-data="{ copied: false }">
                            <span>{{ $account->account_number }}</span>
                            <button type="button" class="gift-copy-btn" @click="navigator.clipboard.writeText('{{ $account->account_number }}'); copied = true; setTimeout(() => copied = false, 2000)">
                                <span x-show="!copied">Salin</span>
                                <span x-show="copied" x-cloak>✓ Tersalin</span>
                            </button>
                        </p>
                        <p class="gift-account-name">a.n. {{ $account->account_name }}</p>
                    </article>
                @empty
                    <p class="gift-empty">Belum ada rekening yang ditambahkan.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
