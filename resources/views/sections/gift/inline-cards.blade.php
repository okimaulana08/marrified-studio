@php
    /** @var \App\Models\Invitation $invitation */
    $accounts = $invitation->giftAccounts;
@endphp

<section id="section-gift" class="section section--gift section--gift-inline" data-section="gift">
    <div class="section-inner section-inner--centered">
        <h2 class="section-title">Tanda Kasih</h2>
        <p class="section-lede">
            Terima kasih telah menambah semangat kegembiraan pernikahan kami
            dengan kehadiran dan hadiah indah Anda.
        </p>

        @forelse ($accounts as $account)
            <article class="gift-card no-swipe" x-data="{ copied: false }">
                <div class="gift-card-icon" aria-hidden="true">
                    @if ($account->type === 'ewallet')
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 18h.01M5 8a3 3 0 003 3h13M5 16h13a3 3 0 003-3V8M3 8a3 3 0 013-3h13"/>
                        </svg>
                    @else
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="6" width="18" height="13" rx="2"/>
                            <path d="M3 10h18M7 15h2"/>
                        </svg>
                    @endif
                </div>
                <div class="gift-card-body">
                    <p class="gift-card-bank">{{ $account->bank_name }}</p>
                    <p class="gift-card-number">{{ $account->account_number }}</p>
                    <p class="gift-card-name">a.n. {{ $account->account_name }}</p>
                </div>
                <button type="button" class="gift-card-copy"
                        @click="navigator.clipboard.writeText('{{ $account->account_number }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        aria-label="Salin nomor rekening">
                    <span x-show="!copied" class="gift-card-copy-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2"/>
                            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                        </svg>
                    </span>
                    <span x-show="copied" x-cloak class="gift-card-copy-ok">✓</span>
                </button>
            </article>
        @empty
            <p class="gift-empty">Belum ada rekening yang ditambahkan.</p>
        @endforelse
    </div>
</section>
