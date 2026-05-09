@php
    /** @var \App\Models\Section $section */
    /** @var \App\Models\Invitation $invitation */
    /** @var \App\Models\Guest|null $guest */
@endphp

<section id="section-rsvp" class="section section--rsvp section--rsvp-card" data-section="rsvp">
    <div class="section-inner section-inner--centered">
        <div class="rsvp-card-wrapper">
            <div class="rsvp-card-icon" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
                    <path d="M5.45 5.11L2 12v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3.45-6.89A2 2 0 0016.76 4H7.24a2 2 0 00-1.79 1.11z"/>
                </svg>
            </div>
            <h2 class="section-title">Konfirmasi Kehadiran</h2>
            <p class="section-lede">
                Mohon konfirmasi kehadiran Anda. Doa restu Anda sangat berarti bagi kami.
            </p>
            <div class="rsvp-card-body">
                @livewire('public.rsvp-form', ['invitation' => $invitation, 'guest' => $guest])
            </div>
        </div>
    </div>
</section>
