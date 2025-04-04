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
        $data = [
            'screenName' => $screenName,
            'entries' => $this->twitterService->importTweets($tweets),
        ];

        if ($cursor) {
            $data['loadNewestLink'] = route('twitter.user', ['screenName' => $screenName]);
        }

        if ($tweets->getBottomCursor()) {
            $data['loadMoreLink'] = route('twitter.user', ['screenName' => $screenName, 'cursor' => $tweets->getBottomCursor()]);
        }

        return view('tweets', $data);
    }

    public function tweet(Request $request, string $screenName, string $tweetId): View
    {
        $cursor = $request->query('cursor');
        $tweets = $this->twitterService->getTweetWithReplies($tweetId, $cursor);
        $data = [
            'screenName' => $screenName,
            'entries' => $this->twitterService->importTweets($tweets),
        ];

        if ($cursor) {
            $data['loadNewestLink'] = route('twitter.tweet', ['screenName' => $screenName, 'tweetId' => $tweetId]);
        }

        if ($tweets->getBottomCursor()) {
            $data['loadMoreLink'] = route('twitter.tweet', [
                'screenName' => $screenName,
                'tweetId' => $tweetId,
                'cursor' => $tweets->getBottomCursor(),
            ]);
        }

        return view('tweets', $data);
    }
}
