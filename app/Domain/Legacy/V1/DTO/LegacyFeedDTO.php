<?php

namespace App\Domain\Legacy\V1\DTO;

use App\Domain\Core\DTO\AuthorDTO;
use App\Domain\Core\DTO\FeedDTO as CoreFeedDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Traits\DTO\HasMetadata;
use App\Domain\Twitter\DTO\TwitterUserFeedDTO;
use App\Support\CompositeId;
use Illuminate\Support\Collection;
use stdClass;

class LegacyFeedDTO extends CoreFeedDTO
{
    use HasMetadata;

    /**
     * @param stdClass $v1FeedAuthor
     * @param stdClass $v1FeedAvatar
     * @param stdClass $v1Feed
     * @param stdClass $v1Entry
     * @return self
     */
    public static function createFromRawDB(
        stdClass $v1FeedAuthor,
        stdClass $v1FeedAvatar,
        stdClass $v1Feed,
        stdClass $v1Entry
    ): self
    {
        $result = new self(
            composite_id: CompositeId::create(ExternalSourceType::TWITTER, $v1Entry->metadata->twitter_user_id),
            author: null,
            name: $v1Feed->name,
            status: FeedStatus::from($v1Feed->status),
            url: $v1Feed->url,
            metadata: [
                'display_name' => "{$v1FeedAuthor->name} (@$v1Feed->name)",
                'profile_image' => $v1FeedAvatar->url,
            ],
        );

        if ($result->status == FeedStatus::ACTIVE) {
            $v2Author = new AuthorDTO(name: $v1FeedAuthor->name, description: $v1FeedAuthor->description);
            $result->author = $v2Author;
        }

        return $result;
    }

    public static function createFromLinkData(string $url): self
    {
        $host = parse_url($url, PHP_URL_HOST);

        return new self(
            composite_id: CompositeId::create(ExternalSourceType::WEB, $host),
            author: null,
            name: $host,
            status: FeedStatus::PREVIEW,
            url: "https://$host",
            metadata: [
                'display_name' => $host ?? null,
                'profile_image' => null,
            ],
        );
    }
}
