<?php

namespace App\Http\Controllers;

use App\Domain\Core\Services\EntryService;
use App\Domain\Twitter\Services\TwitterService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TwitterController extends Controller
{
    public function __construct(protected TwitterService $twitterService, protected EntryService $feedService)
    {
    }

    public function user(Request $request, string $screenName): View
    {
        $cursor = $request->query('cursor');
        $tweets = $this->twitterService->getLatestUserTweetsAndReplies($screenName, $cursor);

        $this->twitterService->importTweets($tweets);

        $data = [
            'isAPI' => true,
            'screenName' => $screenName,
            'entries' => $tweets,
            'title' => "@$screenName"
        ];

        if ($cursor) {
            $data['loadNewestLink'] = route('twitter.user', ['screenName' => $screenName]);
        }

        if ($tweets->bottom_cursor) {
            $data['loadMoreLink'] = route('twitter.user', ['screenName' => $screenName, 'cursor' => $tweets->bottom_cursor]);
        }

        return view('entries', $data);
    }

    public function tweet(Request $request, string $screenName, string $tweetId): View
    {
        $cursor = $request->query('cursor');
        $data = [
            'screenName' => $screenName,
            'entries' => $this->twitterService->getTweetWithReplies($tweetId, $cursor),
            'title' => "@$screenName - $tweetId",
            'loadNewestLink' => route('twitter.tweet', ['screenName' => $screenName, 'tweetId' => $tweetId])
        ];

        $this->twitterService->importTweets($data['entries']);

        if ($data['entries']->bottom_cursor) {
            $data['loadMoreLink'] = route('twitter.tweet', [
                'screenName' => $screenName,
                'tweetId' => $tweetId,
                'cursor' => $data['entries']->bottom_cursor,
            ]);
        }

        return view('entries', $data);
    }
}
