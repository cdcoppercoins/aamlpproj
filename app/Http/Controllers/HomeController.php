<?php

namespace App\Http\Controllers;

use App\Models\HeroSetting;
use App\Models\HeroSlide;
use App\Support\HeroLinkOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $rawCount = DB::table('plates')->count();
        $plateCount = intdiv($rawCount, 50) * 50;

        $heroSlides = $this->resolveHeroSlides();
        $heroIntervalMs = $this->resolveHeroIntervalMs();

        return view('home', [
            'plateCount' => $plateCount,
            'heroSlides' => $heroSlides,
            'heroIntervalMs' => $heroIntervalMs,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resolveHeroSlides(): array
    {
        $slides = Schema::hasTable('hero_slides')
            ? HeroSlide::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
            : collect();

        if ($slides->isEmpty()) {
            $slides = collect(config('home_hero.slides', []));
        }

        return collect($slides)
            ->map(function ($slide) {
                $data = is_array($slide) ? $slide : $slide->toRotatorArray();

                if (($data['route'] ?? null) === 'about') {
                    $data['route'] = 'pages.show';
                    $data['route_params'] = ['slug' => 'about'];
                }

                $data['url'] = HeroLinkOptions::urlFor($data);

                return $data;
            })
            ->values()
            ->all();
    }

    private function resolveHeroIntervalMs(): int
    {
        if (Schema::hasTable('hero_settings')) {
            return HeroSetting::intervalMs();
        }

        return (int) config('home_hero.interval_ms', 7000);
    }
}
