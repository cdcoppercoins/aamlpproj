<ul class="articles-list">
    @foreach ($articles as $article)
        <li class="articles-list-item">
            <article class="articles-card">
                <a href="{{ route('articles.show', $article->slug) }}" class="articles-card-link">
                    @if ($article->heroImageUrl())
                        <div class="articles-card-image-wrap">
                            <img src="{{ $article->heroImageUrl() }}"
                                 alt="{{ $article->hero_image_alt ?: $article->title }}"
                                 class="articles-card-image"
                                 loading="lazy">
                        </div>
                    @endif
                    <div class="articles-card-body">
                        @if ($article->displayDate())
                            <p class="articles-card-date">{{ $article->displayDate() }}</p>
                        @endif
                        <h2 class="articles-card-title">{{ $article->title }}</h2>
                        @if ($article->subtitle)
                            <p class="articles-card-subtitle">{{ $article->subtitle }}</p>
                        @endif
                        <p class="articles-card-author">By {{ $article->author }}</p>
                    </div>
                </a>
            </article>
        </li>
    @endforeach
</ul>
