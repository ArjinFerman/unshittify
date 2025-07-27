<?php

namespace App\Domain\Twitter\DTO;

use App\Domain\Core\DTO\BaseDTO;

class UserDTO extends BaseDTO
{
    public function __construct(
        public string $rest_id,
        public bool $is_blue_verified,
        public string $description,
        public string $favourites_count,
        public string $followers_count,
        public string $friends_count,
        public string $name,
        public array $pinned_tweet_ids_str,
        public ?string $profile_background_color,
        public ?string $profile_banner_url,
        public ?string $profile_image_url_https,
        public ?string $profile_interstitial_type,
        public ?string $profile_link_color,
        public string $screen_name,
        public string $statuses_count,
        public ?string $url,
        public bool $verified,
    )
    {
    }

    public static function fromUserResult(array $data): self
    {
        return new self(
            rest_id: $data['result']['rest_id'],
            is_blue_verified: $data['result']['is_blue_verified'],
            description: $data['result']['legacy']['description'],
            favourites_count: $data['result']['legacy']['favourites_count'],
            followers_count: $data['result']['legacy']['followers_count'],
            friends_count: $data['result']['legacy']['friends_count'],
            name: $data['result']['legacy']['name'],
            pinned_tweet_ids_str: $data['result']['legacy']['pinned_tweet_ids_str'],
            profile_background_color: $data['result']['legacy']['profile_background_color'] ?? null,
            profile_banner_url: $data['result']['legacy']['profile_banner_url'] ?? null,
            profile_image_url_https: $data['result']['legacy']['profile_image_url_https'] ?? null,
            profile_interstitial_type: $data['result']['legacy']['profile_interstitial_type'] ?? null,
            profile_link_color: $data['result']['legacy']['profile_link_color'] ?? null,
            screen_name: $data['result']['legacy']['screen_name'],
            statuses_count: $data['result']['legacy']['statuses_count'],
            url: $data['result']['legacy']['url'] ?? null,
            verified: $data['result']['legacy']['verified'],
        );
    }


}
