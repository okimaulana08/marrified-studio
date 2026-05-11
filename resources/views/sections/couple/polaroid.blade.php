@php
    /** @var \App\Models\Invitation $invitation */
    $couple = $invitation->couple;
    if (! $couple) { return; }
@endphp

<section id="section-couple" class="section section--couple section--couple-polaroid" data-section="couple">
    <div class="section-inner">
        <h2 class="section-title">Mempelai</h2>

        <div class="couple-polaroid-stack">
            <article class="polaroid polaroid--bride">
                <div class="polaroid-tape polaroid-tape--tl"></div>
                <div class="polaroid-photo">
                    @if ($couple->bride_photo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($couple->bride_photo_path) }}" alt="{{ $couple->bride_name }}" loading="lazy">
                    @else
                        <div class="polaroid-photo-placeholder">{{ mb_substr($couple->bride_nickname ?? $couple->bride_name, 0, 1) }}</div>
                    @endif
                </div>
                <div class="polaroid-caption">
                    <p class="polaroid-handwrite">{{ $couple->bride_nickname ?? $couple->bride_name }}</p>
                    <p class="polaroid-fullname">{{ $couple->bride_name }}</p>
                    @if ($couple->bride_parents)
                        <p class="polaroid-parents"><em>Putri dari</em><br>{{ $couple->bride_parents }}</p>
                    @endif
                    @if ($couple->bride_instagram)
                        <a class="couple-ig" href="https://instagram.com/{{ ltrim($couple->bride_instagram, '@') }}" target="_blank" rel="noopener">{{ $couple->bride_instagram }}</a>
                    @endif
                </div>
            </article>

            <div class="polaroid-amp">
                <span class="polaroid-amp-glyph">&amp;</span>
                <span class="polaroid-amp-sub">together</span>
            </div>

            <article class="polaroid polaroid--groom">
                <div class="polaroid-tape polaroid-tape--tr"></div>
                <div class="polaroid-photo">
                    @if ($couple->groom_photo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('invitation_media')->url($couple->groom_photo_path) }}" alt="{{ $couple->groom_name }}" loading="lazy">
                    @else
                        <div class="polaroid-photo-placeholder">{{ mb_substr($couple->groom_nickname ?? $couple->groom_name, 0, 1) }}</div>
                    @endif
                </div>
                <div class="polaroid-caption">
                    <p class="polaroid-handwrite">{{ $couple->groom_nickname ?? $couple->groom_name }}</p>
                    <p class="polaroid-fullname">{{ $couple->groom_name }}</p>
                    @if ($couple->groom_parents)
                        <p class="polaroid-parents"><em>Putra dari</em><br>{{ $couple->groom_parents }}</p>
                    @endif
                    @if ($couple->groom_instagram)
                        <a class="couple-ig" href="https://instagram.com/{{ ltrim($couple->groom_instagram, '@') }}" target="_blank" rel="noopener">{{ $couple->groom_instagram }}</a>
                    @endif
                </div>
            </article>
        </div>
    </div>
</section>
