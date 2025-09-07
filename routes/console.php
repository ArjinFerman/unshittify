<?php

use App\Console\Commands\SyncFeeds;
use Illuminate\Support\Facades\Schedule;

Schedule::command(SyncFeeds::class)->withoutOverlapping()->everyFifteenMinutes();
