<?php

namespace App\Domain\Legacy\V1\DTO;

use App\Domain\Core\DTO\EntryDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Traits\DTO\HasMetadata;
use App\Support\CompositeId;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use stdClass;

class LegacyEntryDTO extends EntryDTO
{
    use HasMetadata;

    public static function createFromLinkData($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $feed = LegacyFeedDTO::createFromLinkData($url);

        new EntryDTO(
            composite_id: CompositeId::create(ExternalSourceType::WEB, $url),
            feed_composite_id: $feed->composite_id,
            url: $url,
            title: $host,
            content: $url,
            published_at: Carbon::now(),
            is_read: false,
            is_starred: false,
            metadata: [
                'thumbnail_url' => null,
            ],
            feed: $feed,
            references: null,
            media: null,
            tags: null
        );
    }
}
