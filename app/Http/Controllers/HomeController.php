<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $rawCount = DB::table('plates')->count();
        $plateCount = intdiv($rawCount, 50) * 50;

        $heroSlides = collect(config('home_hero.slides', []))
            ->map(function (array $slide) {
                $routeName = $slide['route'] ?? null;
                $slide['url'] = $routeName ? route($routeName, $slide['route_params'] ?? []) : null;

                if ($routeName === 'collection.index' && ! Auth::check()) {
                    $slide['url'] = route('login');
                }

                return $slide;
            })
            ->values()
            ->all();

        return view('home', [
            'plateCount' => $plateCount,
            'heroSlides' => $heroSlides,
            'heroIntervalMs' => (int) config('home_hero.interval_ms', 7000),
        ]);
    }
}
