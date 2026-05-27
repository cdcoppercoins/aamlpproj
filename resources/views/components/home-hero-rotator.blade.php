@if (count($heroSlides) > 0)
<section class="home-hero-rotator"
         tabindex="0"
         aria-roledescription="carousel"
         aria-label="Featured miniature license plate sets"
         data-interval="{{ $heroIntervalMs }}"
         data-slide-count="{{ count($heroSlides) }}">
    <div class="home-hero-rotator-viewport">
        @foreach ($heroSlides as $index => $slide)
            <article class="home-hero-slide{{ $index === 0 ? ' is-active' : '' }}{{ ! empty($slide['fill_slide']) ? ' home-hero-slide--fill' : '' }}"
                     id="home-hero-slide-{{ $index }}"
                     aria-roledescription="slide"
                     aria-label="{{ $index + 1 }} of {{ count($heroSlides) }}"
                     @if ($index !== 0) hidden @endif
                     style="--slide-bg: {{ $slide['bg'] }};">
                @if (! empty($slide['url']))
                    <a class="home-hero-slide-link" href="{{ $slide['url'] }}">
                @endif
                    <div class="home-hero-slide-bg" aria-hidden="true"></div>
                    <div class="home-hero-slide-media">
                        <img src="{{ asset($slide['image']) }}"
                             alt="{{ $slide['alt'] }}"
                             class="home-hero-slide-img"
                             width="1150"
                             height="280"
                             @if ($index === 0) fetchpriority="high" @else loading="lazy" @endif
                             decoding="async">
                    </div>
                    <div class="home-hero-slide-caption">
                        <p class="home-hero-slide-headline">{{ $slide['headline'] }}</p>
                        @if (! empty($slide['subline']))
                            <p class="home-hero-slide-subline">{{ $slide['subline'] }}</p>
                        @endif
                        @if (! empty($slide['cta']))
                            <span class="home-hero-slide-cta">{{ $slide['cta'] }} &rarr;</span>
                        @endif
                    </div>
                @if (! empty($slide['url']))
                    </a>
                @endif
            </article>
        @endforeach
    </div>

    @if (count($heroSlides) > 1)
        <button type="button"
                class="home-hero-rotator-btn home-hero-rotator-prev"
                aria-label="Previous slide">
            <span aria-hidden="true">&lsaquo;</span>
        </button>
        <button type="button"
                class="home-hero-rotator-btn home-hero-rotator-next"
                aria-label="Next slide">
            <span aria-hidden="true">&rsaquo;</span>
        </button>

        <div class="home-hero-rotator-dots" role="tablist" aria-label="Choose a slide">
            @foreach ($heroSlides as $index => $slide)
                <button type="button"
                        class="home-hero-rotator-dot{{ $index === 0 ? ' is-active' : '' }}"
                        role="tab"
                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                        aria-controls="home-hero-slide-{{ $index }}"
                        data-slide-to="{{ $index }}"
                        aria-label="Slide {{ $index + 1 }}: {{ $slide['headline'] }}">
                    <span class="home-hero-rotator-dot-fill" aria-hidden="true"></span>
                </button>
            @endforeach
        </div>
    @endif

    <div class="home-hero-rotator-live visually-hidden" aria-live="polite" aria-atomic="true"></div>
</section>
@endif
