<?php

namespace App\Domain\Twitter\DTO;

use App\Domain\Core\DTO\AuthorDTO;
use App\Domain\Core\DTO\FeedDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Enums\FeedStatus;
use App\Support\CompositeId;

class TwitterUserFeedDTO extends FeedDTO
{
    public static function createFromUserResult(array $data): self
    {
        $author = new AuthorDTO(
            name: $data['result']['legacy']['name'],
            description: $data['result']['legacy']['description'],
        );

        return new self(
            composite_id: CompositeId::create(ExternalSourceType::TWITTER, $data['result']['rest_id']),
            author: $author,
            name: $data['result']['legacy']['screen_name'],
            status: FeedStatus::PREVIEW,
            url: config('twitter.base_url') . $data['result']['legacy']['screen_name'],
            metadata: [
                'display_name' => "$author->name (@{$data['result']['legacy']['screen_name']})",
                'profile_image' => $data['result']['legacy']['profile_image_url_https'] ?? null,
                'profile_background_color' => $data['result']['legacy']['profile_background_color'] ?? null,
                'profile_banner_url' => $data['result']['legacy']['profile_banner_url'] ?? null,
                'profile_interstitial_type' => $data['result']['legacy']['profile_interstitial_type'] ?? null,
                'profile_link_color' => $data['result']['legacy']['profile_link_color'] ?? null,
            ],
        );
    }
}
