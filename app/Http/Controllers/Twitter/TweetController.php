<?php

namespace App\Http\Controllers\Twitter;

use App\Domain\Twitter\Services\TwitterService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TweetController extends Controller
{
    public function __construct(protected TwitterService $twitterService)
    {
    }


    public function index(Request $request): View
    {
        return view('welcome', []);
    }

    public function user(string $screenName): View
    {
        $tweets = $this->twitterService->getLatestUserTweets($screenName);
        $entries = $this->twitterService->importTweets($tweets);

        return view('entries', ['entries' => $entries]);
    }
}
