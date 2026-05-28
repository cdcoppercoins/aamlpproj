<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HistoryTimelineEntry extends Model
{
    protected $fillable = [
        'slug',
        'sort_order',
        'label',
        'title',
        'body',
        'image',
        'alt',
        'caption',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return Builder<static>
     */
    public function scopePublishedOrdered(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function imagePath(): ?string
    {
        $image = isset($this->image) ? trim((string) $this->image) : null;

        return $image !== '' ? $image : null;
    }

    public function imageUrl(): ?string
    {
        $path = $this->imagePath();

        return $path ? asset($path) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toTimelineArray(): array
    {
        $imagePath = $this->imagePath();

        return [
            'id' => $this->slug,
            'label' => $this->label,
            'title' => $this->title,
            'body' => $this->body,
            'image_url' => $imagePath ? asset($imagePath) : null,
            'alt' => $this->alt ?: $this->title,
            'caption' => $this->caption ? trim($this->caption) : null,
        ];
    }
}
