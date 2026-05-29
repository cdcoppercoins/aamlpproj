@php
    $searchQuery = $query ?? '';
@endphp
<ul class="articles-list">
    @foreach ($articles as $article)
        <li class="articles-list-item">
            <article class="articles-card">
                <a href="{{ $searchQuery !== '' ? route('articles.show', ['slug' => $article->slug, 'q' => $searchQuery]) : route('articles.show', $article->slug) }}" class="articles-card-link">
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
                        <h2 class="articles-card-title">
                            @if ($searchQuery !== '')
                                {!! \App\Support\SearchHighlighter::highlight($article->title, $searchQuery) !!}
                            @else
                                {{ $article->title }}
                            @endif
                        </h2>
                        @if ($article->subtitle)
                            <p class="articles-card-subtitle">
                                @if ($searchQuery !== '')
                                    {!! \App\Support\SearchHighlighter::highlight($article->subtitle, $searchQuery) !!}
                                @else
                                    {{ $article->subtitle }}
                                @endif
                            </p>
                        @endif
                        <p class="articles-card-author">By
                            @if ($searchQuery !== '')
                                {!! \App\Support\SearchHighlighter::highlight($article->author, $searchQuery) !!}
                            @else
                                {{ $article->author }}
                            @endif
                        </p>
                    </div>
                </a>
            </article>
        </li>
    @endforeach
</ul>
