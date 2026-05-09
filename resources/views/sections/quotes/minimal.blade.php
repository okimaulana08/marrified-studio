@php
    /** @var \App\Models\Section $section */
    $content = $section->content ?? [];
    $text   = $content['text']   ?? 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan hidup dari jenismu sendiri, supaya kamu cenderung dan merasa tenteram kepadanya.';
    $source = $content['source'] ?? 'Q.S. Ar-Rum: 21';
@endphp

<section id="section-quotes" class="section section--quotes section--quotes-minimal" data-section="quotes">
    <div class="section-inner">
        <div class="quotes-minimal-wrap">
            <svg class="quotes-ornament" viewBox="0 0 48 48" fill="currentColor" aria-hidden="true">
                <path d="M14 18c-4.4 0-8 3.6-8 8v10h10V26h-6c0-3.3 2.7-6 6-6V18zm20 0c-4.4 0-8 3.6-8 8v10h10V26h-6c0-3.3 2.7-6 6-6V18z" opacity="0.3"/>
            </svg>
            <blockquote class="quotes-text quotes-text--minimal">{{ $text }}</blockquote>
            <cite class="quotes-source">{{ $source }}</cite>
        </div>
    </div>
</section>
