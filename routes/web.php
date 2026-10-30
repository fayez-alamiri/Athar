<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\MyQuoteController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuoteLikeController;
use App\Http\Controllers\SavedQuoteController;
use Illuminate\Support\Facades\Route;

/* Public discovery and reading */
Route::get('/', [QuoteController::class, 'index'])->name('home');
Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
Route::get('/daily', [QuoteController::class, 'daily'])->name('quotes.daily');
Route::get('/zen', [QuoteController::class, 'zen'])->name('quotes.zen');
Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/authors/{author:slug}', [AuthorController::class, 'show'])->name('authors.show');
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['ar', 'en'])
    ->name('locale.switch');

/* Authentication */
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/* Authenticated publishing, saving, account, and ownership operations */
Route::middleware('auth')->group(function (): void {
    Route::get('/quotes/create/quick', [QuoteController::class, 'createQuick'])->name('quotes.create-quick');
    Route::post('/quotes/quick', [QuoteController::class, 'storeQuick'])->name('quotes.store-quick');
    Route::get('/quotes/create/book', [QuoteController::class, 'createBook'])->name('quotes.create-book');
    Route::post('/quotes/book', [QuoteController::class, 'storeBook'])->name('quotes.store-book');

    Route::post('/quotes/{quote}/like', [QuoteLikeController::class, 'toggle'])->name('quotes.like.toggle');
    Route::post('/quotes/{quote}/save', [SavedQuoteController::class, 'toggle'])->name('quotes.save.toggle');
    Route::get('/saved', [SavedQuoteController::class, 'index'])->name('saved.index');

    Route::get('/my-quotes', [MyQuoteController::class, 'index'])->name('my-quotes.index');
    Route::get('/my-quotes/{quote}/edit', [MyQuoteController::class, 'edit'])->name('my-quotes.edit');
    Route::put('/my-quotes/{quote}', [MyQuoteController::class, 'update'])->name('my-quotes.update');
    Route::delete('/my-quotes/{quote}', [MyQuoteController::class, 'destroy'])->name('my-quotes.destroy');
    Route::delete('/my-quotes', [MyQuoteController::class, 'destroyAll'])->name('my-quotes.destroy-all');

    Route::get('/account', [AccountController::class, 'show'])->name('account.show');
    Route::post('/account/avatar', [AccountController::class, 'updateAvatar'])->name('account.avatar.update');
    Route::delete('/account/avatar', [AccountController::class, 'destroyAvatar'])->name('account.avatar.destroy');
});

/* Lightweight uptime/readiness endpoint used by Render. */
Route::get('/health', HealthController::class)->name('health');
