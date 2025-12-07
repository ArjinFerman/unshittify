<?php

namespace App\Domain\Twitter\DTO;

use App\Domain\Core\DTO\FeedDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Enums\FeedStatus;
use App\Support\CompositeId;

class TwitterLinkFeedDTO extends FeedDTO
{
    public static function createFromTweetResult(array $link, array $tweetCard): self
    {
        $url = getCleanUrl($link['expanded_url']);
        $host = parse_url($url, PHP_URL_HOST);

        return new self(
            composite_id: CompositeId::create(ExternalSourceType::WEB, $host),
            author: null,
            name: $host,
            status: FeedStatus::PREVIEW,
            url: "https://$host",
            metadata: [
                'display_name' => $tweetCard['app_name']['string_value'] ?? null,
                'profile_image' => null,
            ],
        );
    }
}
