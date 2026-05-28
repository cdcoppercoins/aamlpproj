<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    protected $fillable = [
        'slug',
        'author',
        'title',
        'subtitle',
        'body',
        'hero_image',
        'hero_image_alt',
        'sort_order',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<ArticleImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ArticleImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return Builder<static>
     */
    public function scopePublishedOrdered(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->orderByDesc('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * @return Builder<static>
     */
    public function scopeMatchingKeyword(Builder $query, string $keyword): Builder
    {
        $terms = preg_split('/\s+/', trim($keyword), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($terms as $term) {
            $like = '%' . addcslashes($term, '%_\\') . '%';
            $query->where(function (Builder $inner) use ($like): void {
                $inner->where('title', 'like', $like)
                    ->orWhere('subtitle', 'like', $like)
                    ->orWhere('author', 'like', $like)
                    ->orWhere('body', 'like', $like);
            });
        }

        return $query;
    }

    public function heroImagePath(): ?string
    {
        $image = isset($this->hero_image) ? trim((string) $this->hero_image) : null;

        return $image !== '' ? $image : null;
    }

    public function heroImageUrl(): ?string
    {
        $path = $this->heroImagePath();

        return $path ? asset($path) : null;
    }

    public function displayDate(): ?string
    {
        if ($this->published_at) {
            return $this->published_at->format('F j, Y');
        }

        return $this->created_at?->format('F j, Y');
    }
}
