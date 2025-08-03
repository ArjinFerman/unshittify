<?php

namespace App\Domain\Twitter\Strategies;

use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\Feed;
use App\Domain\Core\Strategies\FeedSyncStrategy;
use App\Domain\Twitter\Services\TwitterService;

class TwitterSyncStrategy implements FeedSyncStrategy
{
    protected TwitterService $twitterService;

    public function __construct(protected Feed $feed)
    {
        $this->twitterService = app(TwitterService::class);
    }

    public function sync(): void
    {
        inTransaction(function () {
            /** @var Entry $lastSyncEntry */
            $lastSyncEntry = $this->feed->entries()->orderBy('published_at', 'desc')->first();
            $lastImportedEntry = null;
            $importedCount = 0;
            $cursor = null;

            while ($this->continueImport($importedCount, $lastSyncEntry, $lastImportedEntry)) {
                $tweets = $this->twitterService->getLatestUserTweets($this->feed->name, $cursor);

                $lastImportedEntry = $this->twitterService->importTweets($tweets)->last();
                $cursor = $tweets->getBottomCursor();
                $importedCount += $tweets->count();
            }
        });
    }

    protected function continueImport(int $importedCount, ?Entry $lastSyncEntry, ?Entry $lastImportedEntry): bool
    {
        return (
            $importedCount < config('app.feeds.max_entries_per_sync')
            && (!$lastSyncEntry || !$lastImportedEntry || $lastImportedEntry->published_at >= $lastSyncEntry->published_at)
        );
    }
}
