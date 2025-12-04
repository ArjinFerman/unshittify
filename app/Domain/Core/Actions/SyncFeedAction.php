<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\Feed;
use App\Domain\Twitter\Services\TwitterService;

class SyncFeedAction extends BaseAction
{
    const MAX_IMPORT = 100;

    protected int $importedCount = 0;
    protected ?Entry $lastEntry = null;
    protected ?Entry $lastImportedEntry = null;

    public function __construct(public TwitterService $twitterService)
    {
    }

    /**
     * @throws \Throwable
     */
    public function execute(Feed $feed): void
    {
        if ($feed->type != ExternalSourceType::TWITTER)
            return;

        inTransaction(function () use ($feed) {
            /** @var Entry $lastEntry */
            $this->lastEntry = $feed->entries()->orderBy('published_at', 'desc')->first();
            $cursor = null;

            while ($this->continueImport()) {
                $tweets = $this->twitterService->getLatestUserTweets($feed->name, $cursor);
                $this->lastImportedEntry = $this->twitterService->importTweets($tweets)->last();
                $cursor = $tweets->bottom_cursor;
            }
        });
    }

    protected function continueImport()
    {
        return (
            $this->importedCount < self::MAX_IMPORT
            && (!$this->lastEntry || !$this->lastImportedEntry || $this->lastImportedEntry->published_at >= $this->lastEntry->published_at)
        );
    }
}
