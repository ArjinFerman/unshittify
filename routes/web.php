<?php

use App\Http\Controllers\FeedController;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\Twitter\TweetController;
use Illuminate\Support\Facades\Route;

Route::name('twitter.')->prefix('twitter')->group(function () {
    Route::get('/', [TweetController::class, 'index'])->name('index');
    Route::get('/{screenName}', [TweetController::class, 'user'])->name('user');
});

Route::name('core.')->group(function () {
    Route::get('/', [FeedController::class, 'index'])->name('latest');
    Route::get('/proxy/{url}', [ProxyController::class, 'index'])->name('proxy');
});
