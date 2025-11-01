<?php

use App\Http\Controllers\EntryController;
use App\Http\Controllers\TwitterController;
use Illuminate\Support\Facades\Route;


Route::name('core.')->group(function () {
    Route::get('/', [EntryController::class, 'index'])->name('latest');
    Route::get('/starred', [EntryController::class, 'starred'])->name('starred');
});

Route::name('twitter.')->prefix('twitter')->group(function () {
    Route::get('/', [TwitterController::class, 'index'])->name('index');
    Route::get('/{screenName}', [TwitterController::class, 'user'])->name('user');
    Route::get('/{screenName}/status/{tweetId}', [TwitterController::class, 'tweet'])->name('tweet');
});

Route::twitterFullUrlRoutes();
