<?php

use App\Http\Controllers\EntryController;
use App\Http\Controllers\TwitterController;
use Illuminate\Support\Facades\Route;


Route::name('core.')->group(function () {
    Route::get('/', [EntryController::class, 'index'])->name('latest');
    Route::get('/starred', [EntryController::class, 'starred'])->name('starred');
});

Route::name('twitter.')->prefix('twitter')->group(function () {
    Route::get('/{feedName}', [TwitterController::class, 'feed'])->name('feed');
    Route::get('/{feedName}/status/{entryId}', [TwitterController::class, 'entry'])->name('entry');
});

Route::twitterFullUrlRoutes();
