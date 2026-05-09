@php
    /** @var \App\Models\Invitation $invitation */
    $couple = $invitation->couple;
    if (! $couple) { return; }
@endphp

<section id="section-couple" class="section section--couple" data-section="couple">
    <div class="section-inner">
        <h2 class="section-title">Mempelai</h2>

        <div class="couple-grid">
            <article class="couple-person couple-person--bride">
                <div class="couple-photo">
                    @if ($couple->bride_photo_path)
                        <img src="{{ asset('storage/'.$couple->bride_photo_path) }}" alt="{{ $couple->bride_name }}" loading="lazy">
                    @else
                        <div class="couple-photo-placeholder">{{ mb_substr($couple->bride_nickname ?? $couple->bride_name, 0, 1) }}</div>
                    @endif
                </div>
                <h3 class="couple-name couple-name--script">{{ $couple->bride_nickname ?? $couple->bride_name }}</h3>
                <p class="couple-fullname">{{ $couple->bride_name }}</p>
                @if ($couple->bride_parents)
                    <p class="couple-parents"><em>Putri dari</em><br>{{ $couple->bride_parents }}</p>
                @endif
                @if ($couple->bride_instagram)
                    <a class="couple-ig" href="https://instagram.com/{{ ltrim($couple->bride_instagram, '@') }}" target="_blank" rel="noopener">{{ $couple->bride_instagram }}</a>
                @endif
            </article>

            <div class="couple-and"><span>&amp;</span></div>

            <article class="couple-person couple-person--groom">
                <div class="couple-photo">
                    @if ($couple->groom_photo_path)
                        <img src="{{ asset('storage/'.$couple->groom_photo_path) }}" alt="{{ $couple->groom_name }}" loading="lazy">
                    @else
                        <div class="couple-photo-placeholder">{{ mb_substr($couple->groom_nickname ?? $couple->groom_name, 0, 1) }}</div>
                    @endif
                </div>
                <h3 class="couple-name couple-name--script">{{ $couple->groom_nickname ?? $couple->groom_name }}</h3>
                <p class="couple-fullname">{{ $couple->groom_name }}</p>
                @if ($couple->groom_parents)
                    <p class="couple-parents"><em>Putra dari</em><br>{{ $couple->groom_parents }}</p>
                @endif
                @if ($couple->groom_instagram)
                    <a class="couple-ig" href="https://instagram.com/{{ ltrim($couple->groom_instagram, '@') }}" target="_blank" rel="noopener">{{ $couple->groom_instagram }}</a>
                @endif
            </article>
        </div>
    </div>
</section>
