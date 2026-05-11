@php
    /** @var \App\Models\Invitation $invitation */
    $couple = $invitation->couple;
    if (! $couple) { return; }
@endphp

<section id="section-couple" class="section section--couple section--couple-mirror-arch" data-section="couple">
    <div class="section-inner">
        <h2 class="section-title">Mempelai</h2>

        <div class="mirror-arch-grid">
            <article class="mirror-arch-person mirror-arch-person--bride">
                <div class="mirror-arch-photo">
                    <div class="mirror-arch-frame"></div>
                    @if ($couple->bride_photo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($couple->bride_photo_path) }}" alt="{{ $couple->bride_name }}" loading="lazy">
                    @else
                        <div class="mirror-arch-photo-placeholder">{{ mb_substr($couple->bride_nickname ?? $couple->bride_name, 0, 1) }}</div>
                    @endif
                </div>
                <p class="mirror-arch-eyebrow">The Bride</p>
                <h3 class="mirror-arch-name">{{ $couple->bride_nickname ?? $couple->bride_name }}</h3>
                <p class="mirror-arch-fullname">{{ $couple->bride_name }}</p>
                @if ($couple->bride_parents)
                    <div class="mirror-arch-rule"></div>
                    <p class="mirror-arch-parents"><em>Putri dari</em><br>{{ $couple->bride_parents }}</p>
                @endif
                @if ($couple->bride_instagram)
                    <a class="couple-ig" href="https://instagram.com/{{ ltrim($couple->bride_instagram, '@') }}" target="_blank" rel="noopener">{{ $couple->bride_instagram }}</a>
                @endif
            </article>

            <div class="mirror-arch-crest" aria-hidden="true">
                <svg viewBox="0 0 60 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M30 4 C20 22, 8 28, 4 40 C8 52, 20 58, 30 76 C40 58, 52 52, 56 40 C52 28, 40 22, 30 4 Z"
                          stroke="currentColor" stroke-width="1" fill="none" opacity="0.55"/>
                    <text x="30" y="46" text-anchor="middle" font-family="var(--fs, serif)" font-size="22"
                          fill="currentColor" opacity="0.95">&amp;</text>
                </svg>
            </div>

            <article class="mirror-arch-person mirror-arch-person--groom">
                <div class="mirror-arch-photo">
                    <div class="mirror-arch-frame"></div>
                    @if ($couple->groom_photo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($couple->groom_photo_path) }}" alt="{{ $couple->groom_name }}" loading="lazy">
                    @else
                        <div class="mirror-arch-photo-placeholder">{{ mb_substr($couple->groom_nickname ?? $couple->groom_name, 0, 1) }}</div>
                    @endif
                </div>
                <p class="mirror-arch-eyebrow">The Groom</p>
                <h3 class="mirror-arch-name">{{ $couple->groom_nickname ?? $couple->groom_name }}</h3>
                <p class="mirror-arch-fullname">{{ $couple->groom_name }}</p>
                @if ($couple->groom_parents)
                    <div class="mirror-arch-rule"></div>
                    <p class="mirror-arch-parents"><em>Putra dari</em><br>{{ $couple->groom_parents }}</p>
                @endif
                @if ($couple->groom_instagram)
                    <a class="couple-ig" href="https://instagram.com/{{ ltrim($couple->groom_instagram, '@') }}" target="_blank" rel="noopener">{{ $couple->groom_instagram }}</a>
                @endif
            </article>
        </div>
    </div>
</section>
