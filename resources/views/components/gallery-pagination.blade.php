@php
    $currentPage = $results->currentPage();
    $lastPage = $results->lastPage();
    $windowSize = min(5, $lastPage);
    $start = max(1, min($currentPage - intdiv($windowSize, 2), $lastPage - $windowSize + 1));
    $end = min($lastPage, $start + $windowSize - 1);
    $start = max(1, $end - $windowSize + 1);
    $showJumpButtons = $lastPage > 5;
    $showFirstJump = $showJumpButtons && $start > 1;
    $showLastJump = $showJumpButtons && $end < $lastPage;
@endphp

<nav class="gallery-pagination" aria-label="Search results pages">
    @if ($currentPage <= 1)
        <span class="gallery-page-btn is-arrow is-disabled" aria-hidden="true">&lsaquo;</span>
    @else
        <a class="gallery-page-btn is-arrow" href="{{ $results->previousPageUrl() }}" aria-label="Previous page">&lsaquo;</a>
    @endif

    @if ($showFirstJump)
        <a class="gallery-page-btn is-jump" href="{{ $results->url(1) }}" aria-label="First page">&laquo;</a>
    @endif

    @for ($page = $start; $page <= $end; $page++)
        @if ($page === $currentPage)
            <span class="gallery-page-btn is-current" aria-current="page">{{ $page }}</span>
        @else
            <a class="gallery-page-btn" href="{{ $results->url($page) }}">{{ $page }}</a>
        @endif
    @endfor

    @if ($showLastJump)
        <a class="gallery-page-btn is-jump" href="{{ $results->url($lastPage) }}" aria-label="Last page">&raquo;</a>
    @endif

    @if ($currentPage >= $lastPage)
        <span class="gallery-page-btn is-arrow is-disabled" aria-hidden="true">&rsaquo;</span>
    @else
        <a class="gallery-page-btn is-arrow" href="{{ $results->nextPageUrl() }}" aria-label="Next page">&rsaquo;</a>
    @endif
</nav>
