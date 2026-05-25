<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ContributeController;
use App\Http\Controllers\NewsletterSubscriberController;

Route::get('/', function () {
    $rawCount = \Illuminate\Support\Facades\DB::table('plates')->count();
    $plateCount = intdiv($rawCount, 50) * 50;

    return view('home', ['plateCount' => $plateCount]);
})->name('home');

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::get('/gallery/{setName}', [GalleryController::class, 'show'])->name('gallery.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/sitemap.xml', \App\Http\Controllers\SitemapController::class)->name('sitemap');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/history', function () {
    return view('history');
})->name('history');

Route::get('/contribute', [ContributeController::class, 'index'])->name('contribute');
Route::post('/contribute', [ContributeController::class, 'store'])->name('contribute.store');
Route::post('/newsletter/subscribe', [NewsletterSubscriberController::class, 'store'])->name('newsletter.subscribe');
