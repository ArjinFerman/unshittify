<?php

use App\Http\Controllers\Twitter\TweetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TweetController::class, 'index'])->name('twitter');
Route::get('/{screenName}', [TweetController::class, 'user'])->name('twitter');
