<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSetting extends Model
{
    protected $fillable = [
        'interval_ms',
    ];

    protected function casts(): array
    {
        return [
            'interval_ms' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'interval_ms' => (int) config('home_hero.interval_ms', 7000),
        ]);
    }

    public static function intervalMs(): int
    {
        return static::current()->interval_ms;
    }
}
