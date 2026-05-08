<section id="section-rsvp" class="section section--rsvp" data-section="rsvp">
    <x-theme.section-decorations :theme="$theme" sectionKey="rsvp" />

    <div class="section-inner section-inner--centered">
        <x-theme.section-icon :theme="$theme" sectionKey="rsvp" size="48px" />
        <h2 class="section-title">Konfirmasi Kehadiran</h2>
        <p class="section-lede">Mohon konfirmasi kehadiran Anda dengan mengisi form di bawah ini.</p>

        @livewire('public.rsvp-form', ['invitation' => $invitation, 'guest' => $guest])
    </div>
</section>
