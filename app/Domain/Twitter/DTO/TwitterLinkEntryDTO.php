<?php

namespace App\Domain\Twitter\DTO;

use App\Domain\Core\DTO\EntryDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Support\CompositeId;
use Carbon\Carbon;

class TwitterLinkEntryDTO extends EntryDTO
{
    /**
     * @param array $linkData
     * @return EntryDTO
     */
    public static function createFromTweetResult(array $linkData, $tweetCard): EntryDTO
    {
        $url = getCleanUrl($linkData['expanded_url']);
        $feed = TwitterLinkFeedDTO::createFromTweetResult($linkData, $tweetCard);

        $linkDto = new self(
            composite_id: CompositeId::create(ExternalSourceType::WEB, sha1($url)),
            feed_composite_id: $feed->composite_id,
            url: $url,
            title: $tweetCard['title']['string_value'] ?? null,
            content: $tweetCard['description']['string_value'] ?? null,
            published_at: Carbon::now(),
            is_read: false,
            is_starred: false,
            metadata: [
                'thumbnail_url' => $tweetCard['player_image_large']['image_value']['url'] ?? null,
            ],
            feed: $feed,
            references: null,
            media: null,
            tags: null
        );

        return $linkDto;
    }
}
