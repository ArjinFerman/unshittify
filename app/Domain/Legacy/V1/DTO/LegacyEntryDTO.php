<?php

namespace App\Domain\Legacy\V1\DTO;

use App\Domain\Core\DTO\EntryDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Traits\DTO\HasMetadata;
use App\Support\CompositeId;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;

class LegacyEntryDTO extends EntryDTO
{
    use HasMetadata;

    public static function createFromLinkData($url): self
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (Str::length($url) > 2048) {
            Log::warning('LegacyEntryDTO createFromLinkData: URL is too long: ' . $url);
            $url = Str::substr($url, 0, 2048);
        }

        $feed = LegacyFeedDTO::createFromLinkData($url);

        return new self(
            composite_id: CompositeId::create(ExternalSourceType::WEB, sha1($url)),
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
