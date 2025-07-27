<?php

namespace App\Http\Controllers\Twitter;

use App\Domain\Core\Services\FeedService;
use App\Domain\Twitter\Services\TwitterService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TweetController extends Controller
{
    public function __construct(protected TwitterService $twitterService, protected FeedService $feedService)
    {
    }


    public function index(Request $request): View
    {
        return view('welcome', []);
    }

    public function user(Request $request, string $screenName): View
    {
        $cursor = $request->query('cursor');
        $tweets = $this->twitterService->getLatestUserTweetsAndReplies($screenName, $cursor);

        $this->twitterService->importTweets($tweets, true);

        $data = [
            'screenName' => $screenName,
            'entries' => $tweets,
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
        $data = [
            'screenName' => $screenName,
            'entries' => $this->twitterService->getTweetWithReplies($tweetId, $cursor),
        ];

        $this->twitterService->importTweets($data['entries']);

        if ($cursor) {
            $data['loadNewestLink'] = route('twitter.tweet', ['screenName' => $screenName, 'tweetId' => $tweetId]);
        }

        if ($data['entries']->getBottomCursor()) {
            $data['loadMoreLink'] = route('twitter.tweet', [
                'screenName' => $screenName,
                'tweetId' => $tweetId,
                'cursor' => $data['entries']->getBottomCursor(),
            ]);
        }

        return view('tweets', $data);
    }
}
