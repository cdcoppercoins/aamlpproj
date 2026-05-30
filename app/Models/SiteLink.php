<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteLink extends Model
{
    public const PLACEMENTS = [
        'footer_left' => 'Footer — left column',
        'footer_right' => 'Footer — right column',
        'header_more' => 'Header — More menu',
    ];

    protected $fillable = [
        'label',
        'page_id',
        'url',
        'placement',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function href(): ?string
    {
        if (! $this->is_published) {
            return null;
        }

        if ($this->page_id && $this->page && $this->page->is_published) {
            return $this->page->publicUrl();
        }

        return $this->normalizedUrl();
    }

    public function isExternal(): bool
    {
        $href = $this->href();

        return is_string($href) && preg_match('#^https?://#i', $href) === 1;
    }

    public function destinationLabel(): string
    {
        if ($this->page_id && $this->page) {
            return '/'.$this->page->slug;
        }

        return (string) ($this->url ?? '');
    }

    private function normalizedUrl(): ?string
    {
        $url = trim((string) ($this->url ?? ''));

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return $url;
        }

        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }

        return null;
    }

    public function scopePublished($query)
    {
        return $query
            ->where('is_published', true)
            ->where(function ($query) {
                $query
                    ->where(function ($pageLinks) {
                        $pageLinks
                            ->whereNotNull('page_id')
                            ->whereHas('page', fn ($pageQuery) => $pageQuery->where('is_published', true));
                    })
                    ->orWhere(function ($urlLinks) {
                        $urlLinks
                            ->whereNull('page_id')
                            ->whereNotNull('url');
                    });
            });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}
