<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = [
        'sort_order',
        'image',
        'alt',
        'headline',
        'subline',
        'cta',
        'route',
        'route_params',
        'bg',
        'is_active',
        'fill_slide',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'route_params' => 'array',
            'is_active' => 'boolean',
            'fill_slide' => 'boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toRotatorArray(): array
    {
        return [
            'image' => $this->image,
            'alt' => $this->alt,
            'headline' => $this->headline,
            'subline' => $this->subline,
            'cta' => $this->cta,
            'route' => $this->route,
            'route_params' => $this->route_params ?? [],
            'bg' => $this->bg,
            'fill_slide' => $this->fill_slide,
        ];
    }
}
