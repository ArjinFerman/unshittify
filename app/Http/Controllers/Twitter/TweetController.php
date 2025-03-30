<?php

namespace App\Http\Controllers\Twitter;

use App\Domain\Twitter\Services\TwitterService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class TweetController extends Controller
{
    public function __construct(protected TwitterService $twitterService)
    {
    }


    public function index(Request $request): View
    {
        return view('welcome', []);
    }

    public function user(Request $request, string $screenName): View
    {
        $cursor = $request->query('cursor');

        $tweets = $this->twitterService->getLatestUserTweets($screenName, $cursor);
        $entries = $this->twitterService->importTweets($tweets);

        return view('tweets', [
            'screenName' => $screenName,
            'entries' => $entries,
            'currentCursor' => $cursor,
            'bottomCursor' => $tweets->getBottomCursor(),
        ]);
    }
}
