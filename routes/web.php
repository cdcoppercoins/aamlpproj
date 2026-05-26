<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ContributeController;
use App\Http\Controllers\NewsletterSubscriberController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;

Route::get('/', [HomeController::class, 'index'])->name('home');

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

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
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
