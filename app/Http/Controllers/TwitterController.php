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

    public function feed(Request $request, string $feedName): View
    {
        $cursor = $request->query('cursor');
        $tweets = $this->twitterService->getLatestUserTweetsAndReplies($feedName, $cursor);

        $this->twitterService->importTweets($tweets);

        $data = [
            'isAPI' => true,
            'entryName' => $feedName,
            'entries' => $tweets,
            'title' => "@$feedName"
        ];

        if ($cursor) {
            $data['loadNewestLink'] = route('twitter.feed', ['feedName' => $feedName]);
        }

        if ($tweets->bottom_cursor) {
            $data['loadMoreLink'] = route('twitter.feed', ['feedName' => $feedName, 'cursor' => $tweets->bottom_cursor]);
        }

        return view('entries', $data);
    }

    public function entry(Request $request, string $feedName, string $entryId): View
    {
        $cursor = $request->query('cursor');
        $data = [
            'feedName' => $feedName,
            'entries' => $this->twitterService->getTweetWithReplies($entryId, $cursor),
            'title' => "@$feedName - $entryId",
            'loadNewestLink' => route('twitter.entry', ['feedName' => $feedName, 'entryId' => $entryId])
        ];

        $this->twitterService->importTweets($data['entries']);

        if ($data['entries']->bottom_cursor) {
            $data['loadMoreLink'] = route('twitter.entry', [
                'feedName' => $feedName,
                'entryId' => $entryId,
                'cursor' => $data['entries']->bottom_cursor,
            ]);
        }

        return view('entries', $data);
    }
}
