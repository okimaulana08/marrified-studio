<section id="section-rsvp" class="section section--rsvp" data-section="rsvp">
    <div class="section-inner section-inner--centered">
        <h2 class="section-title">Konfirmasi Kehadiran</h2>
        <p class="section-lede">Mohon konfirmasi kehadiran Anda dengan mengisi form di bawah ini.</p>

        @livewire('public.rsvp-form', ['invitation' => $invitation, 'guest' => $guest])
    </div>
</section>
