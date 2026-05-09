@php
    /** @var \App\Models\Invitation $invitation */
    /** @var \App\Models\Guest|null $guest */
    $messages = $invitation->guestbookMessages()->where('is_visible', true)->latest()->take(20)->get();

    // Sticky-note pastel rotation for variety. Stable per-message by index modulo
    // so the list doesn't jitter on re-render.
    $palette = ['#fef3c7', '#fee2e2', '#dbeafe', '#dcfce7', '#fce7f3', '#ede9fe'];
    $rotations = [-2.4, 1.6, -1.1, 2.2, -1.8, 0.9, 1.4, -2.0];
@endphp

<section id="section-guestbook" class="section section--guestbook section--guestbook-wall" data-section="guestbook">
    <div class="section-inner">
        <h2 class="section-title">Ucapan &amp; Doa</h2>

        @livewire('public.guestbook-form', ['invitation' => $invitation, 'guest' => $guest])

        <div class="guestbook-wall">
            @forelse ($messages as $i => $msg)
                <article class="guestbook-note"
                         style="background: {{ $palette[$i % count($palette)] }};
                                transform: rotate({{ $rotations[$i % count($rotations)] }}deg);">
                    <p class="guestbook-note-text">{{ $msg->message }}</p>
                    <footer class="guestbook-note-foot">
                        <span class="guestbook-note-name">— {{ $msg->name }}</span>
                        <span class="guestbook-note-time">{{ $msg->created_at?->diffForHumans() }}</span>
                    </footer>
                </article>
            @empty
                <p class="guestbook-empty">Jadilah yang pertama mengirim ucapan!</p>
            @endforelse
        </div>
    </div>
</section>
