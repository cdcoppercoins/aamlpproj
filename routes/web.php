<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeHeroController;
use App\Http\Controllers\Admin\CatalogImportController;
use App\Http\Controllers\Admin\CatalogPlateController;
use App\Http\Controllers\Admin\CatalogSetController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ContributeController;
use App\Http\Controllers\NewsletterSubscriberController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HistoryController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::get('/gallery/{setName}', [GalleryController::class, 'show'])->name('gallery.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/sitemap.xml', \App\Http\Controllers\SitemapController::class)->name('sitemap');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/history', [HistoryController::class, 'index'])->name('history');

Route::get('/contribute', [ContributeController::class, 'index'])->name('contribute');
Route::post('/contribute', [ContributeController::class, 'store'])->name('contribute.store');
Route::post('/newsletter/subscribe', [NewsletterSubscriberController::class, 'store'])->name('newsletter.subscribe');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'not.blocked'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/collection/members/{username}', [CollectionController::class, 'showMember'])->name('collection.members.show');
    Route::put('/collection/sets/{setCode}/visibility', [CollectionController::class, 'updateSetVisibility'])->name('collection.set.visibility');
    Route::get('/collection/manage', [CollectionController::class, 'manage'])->name('collection.manage');
    Route::get('/collection/manage/pdf', [CollectionController::class, 'managePdf'])->name('collection.manage.pdf');
    Route::put('/collection/manage', [CollectionController::class, 'updateManage'])->name('collection.manage.update');
    Route::post('/collection/manage/fill', [CollectionController::class, 'fillManageSet'])->name('collection.manage.fill');
    Route::get('/collection', [CollectionController::class, 'index'])->name('collection.index');
    Route::post('/collection', [CollectionController::class, 'store'])->name('collection.store');
    Route::get('/collection/{collectionItem}/edit', [CollectionController::class, 'edit'])->name('collection.edit');
    Route::put('/collection/{collectionItem}', [CollectionController::class, 'update'])->name('collection.update');
    Route::delete('/collection/{collectionItem}', [CollectionController::class, 'destroy'])->name('collection.destroy');
});

Route::middleware(['auth', 'not.blocked', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('/newsletter', [AdminNewsletterController::class, 'index'])->name('newsletter.index');
    Route::delete('/newsletter/{subscriber}', [AdminNewsletterController::class, 'destroy'])->name('newsletter.destroy');

    Route::get('/home-hero', [HomeHeroController::class, 'index'])->name('home-hero.index');
    Route::put('/home-hero/settings', [HomeHeroController::class, 'updateSettings'])->name('home-hero.settings.update');
    Route::get('/home-hero/create', [HomeHeroController::class, 'create'])->name('home-hero.create');
    Route::post('/home-hero', [HomeHeroController::class, 'store'])->name('home-hero.store');
    Route::get('/home-hero/{heroSlide}/edit', [HomeHeroController::class, 'edit'])->name('home-hero.edit');
    Route::match(['put', 'post'], '/home-hero/{heroSlide}', [HomeHeroController::class, 'update'])->name('home-hero.update');
    Route::delete('/home-hero/{heroSlide}', [HomeHeroController::class, 'destroy'])->name('home-hero.destroy');

    Route::prefix('catalog')->name('catalog.')->group(function () {
        Route::get('import', [CatalogImportController::class, 'create'])->name('import.create');
        Route::post('import', [CatalogImportController::class, 'store'])->name('import.store');
        Route::get('sets', [CatalogSetController::class, 'index'])->name('sets.index');
        Route::get('sets/create', [CatalogSetController::class, 'create'])->name('sets.create');
        Route::post('sets', [CatalogSetController::class, 'store'])->name('sets.store');
        Route::get('plates/{plate}/edit', [CatalogPlateController::class, 'edit'])->name('plates.edit');
        Route::put('plates/{plate}', [CatalogPlateController::class, 'update'])->name('plates.update');
        Route::delete('plates/{plate}', [CatalogPlateController::class, 'destroy'])->name('plates.destroy');
        Route::get('sets/{setCode}/plates/create', [CatalogPlateController::class, 'create'])->name('plates.create');
        Route::post('sets/{setCode}/plates', [CatalogPlateController::class, 'store'])->name('plates.store');
        Route::get('sets/{setCode}/edit', [CatalogSetController::class, 'edit'])->name('sets.edit');
        Route::put('sets/{setCode}', [CatalogSetController::class, 'update'])->name('sets.update');
        Route::delete('sets/{setCode}', [CatalogSetController::class, 'destroy'])->name('sets.destroy');
        Route::get('sets/{setCode}', [CatalogSetController::class, 'show'])->name('sets.show');
    });
});
