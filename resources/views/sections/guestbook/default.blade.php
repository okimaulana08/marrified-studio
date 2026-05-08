@php
    /** @var \App\Models\Invitation $invitation */
    $messages = $invitation->guestbookMessages()->where('is_visible', true)->latest()->take(20)->get();
@endphp

<section id="section-guestbook" class="section section--guestbook" data-section="guestbook">
    <x-theme.section-decorations :theme="$theme" sectionKey="guestbook" />

    <div class="section-inner">
        <x-theme.section-icon :theme="$theme" sectionKey="guestbook" size="48px" />
        <h2 class="section-title">Ucapan &amp; Doa</h2>

        @livewire('public.guestbook-form', ['invitation' => $invitation, 'guest' => $guest])

        <div class="guestbook-list">
            @forelse ($messages as $msg)
                <article class="guestbook-message">
                    <p class="guestbook-name">{{ $msg->name }}</p>
                    <p class="guestbook-text">{{ $msg->message }}</p>
                    <p class="guestbook-time">{{ $msg->created_at?->diffForHumans() }}</p>
                </article>
            @empty
                <p class="guestbook-empty">Jadilah yang pertama mengirim ucapan!</p>
            @endforelse
        </div>
    </div>
</section>
