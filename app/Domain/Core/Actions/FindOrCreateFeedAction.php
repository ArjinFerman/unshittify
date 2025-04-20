<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Enums\FeedType;
use App\Domain\Core\Models\Author;
use App\Domain\Core\Models\Feed;

class FindOrCreateFeedAction extends BaseAction
{
    /**
     * @throws \Throwable
     */
    public function execute(string $url, FeedType $type, Author $author, string $name): Feed
    {
        return $this->optionalTransaction(function () use ($url, $type, $author, $name) {
            $feed = Feed::whereUrl($url)
                ->whereType($type)
                ->first();

            if (!$feed) {
                $feed = new Feed;
                $feed->url = $url;
                $feed->author_id = $author->id;
                $feed->type = $type;
                $feed->status = FeedStatus::PREVIEW;
                $feed->name = $name;
                $feed->save();
            }

            return $feed;
        });
    }
}
