<?php

namespace App\Domain\Twitter\DTO;

use App\Domain\Core\DTO\BaseDTO;
use App\Domain\Core\DTO\MediaCollectionDTO;
use App\Domain\Twitter\Support\DTO\MediaParser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TweetDTO extends BaseDTO
{
    public function __construct(
        public ?string $rest_id = null,
        public ?string $conversation_id_str = null,
        public ?Carbon $created_at = null,
        public ?string $full_text = null,
        public bool $is_quote_status = false,
        public ?string $quoted_status_id_str = null,
        public ?string $user_id_str = null,
        public ?string $reply_to_id_str = null,
        public ?TweetDTO $quoted_tweet = null,
        public ?TweetDTO $retweet = null,
        public ?UserDTO $author = null,
        public ?MediaCollectionDTO $media = null,
        /** @var array<LinkDTO> $links */
        public ?array $links = null,
    )
    {
    }

    public function getTweetUrl(): string
    {
        return config('twitter.base_url') . "{$this->author->screen_name}/status/{$this->rest_id}";
    }

    public static function fromTweetResult(array $data): self
    {
        if(!isset($data['rest_id'])) {
            // Case of `$data['__typename'] == 'TweetWithVisibilityResults'`, but there might be more in the future
            $data = $data['tweet'];
        }

        $quoted_tweet = null;
        if(!empty($data['quoted_status_result'])) {
            $quoted_tweet = TweetDTO::fromTweetResult($data['quoted_status_result']['result']);
        }

        $retweet = null;
        if(!empty($data['legacy']['retweeted_status_result'])) {
            $retweet = TweetDTO::fromTweetResult($data['legacy']['retweeted_status_result']['result']);
        }

        $author = null;
        $userResults = $data['core']['user_result'] ?? $data['core']['user_results'] ?? null;
        if($userResults) {
            $author = UserDTO::fromUserResult($userResults);
        }

        $content = e(htmlspecialchars_decode($data['note_tweet']['note_tweet_results']['result']['text'] ?? $data['legacy']['full_text']));

        $mediaCollection = new MediaCollectionDTO;
        foreach ($data['legacy']['extended_entities']['media'] ?? [] as $media) {
            $type = MediaParser::getMediaType($media);
            if ($type) {
                $mediaCollection = $mediaCollection->merge(MediaParser::mediaDTOCollectionFromTwitter($media));
                $content = Str::replace($media['url'], "<x-media mediaObjectId=\"twitter-{$media['id_str']}\"/>", $content);
            } else {
                Log::warning("Unsupported media type: {$media['type']}");
            }
        }

        $tweetCard = [];
        if (isset($data['tweet_card'])) {
            foreach ($data['tweet_card']['legacy']['binding_values'] as $binding_value) {
                $tweetCard[$binding_value['key']] = $binding_value['value'];
            }
        }

        $entities = $data['note_tweet']['note_tweet_results']['result']['entity_set'] ?? $data['legacy']['entities'];
        $links = [];
        foreach ($entities['urls'] as $link) {
            $linkDto = new LinkDTO($link['url']);
            $linkDto->expanded_url = getCleanUrl($link['expanded_url']);

            if (isset($tweetCard['vanity_url']) && isset($tweetCard['card_url']) && !isset($tweetCard['broadcast_id']) && $tweetCard['card_url']['string_value'] == $linkDto->url) {
                $linkDto->author = $tweetCard['vanity_url']['string_value'];
                $linkDto->title = $tweetCard['title']['string_value'];
                $linkDto->description = $tweetCard['description']['string_value'] ?? null;
                $linkDto->thumbnail_url = isset($tweetCard['thumbnail_image_original']) ? $tweetCard['thumbnail_image_original']['image_value']['url'] : null;
            }

            $links[] = $linkDto;
            $content = Str::replace($link['url'], "<x-entry.link url=\"{$linkDto->expanded_url}\"/>", $content);
        }

        return new self(
            rest_id: $data['rest_id'],
            conversation_id_str: $data['legacy']['conversation_id_str'],
            created_at: Carbon::parse($data['legacy']['created_at']),
            full_text: $content,
            is_quote_status: $data['legacy']['is_quote_status'],
            quoted_status_id_str: $data['legacy']['quoted_status_id_str'] ?? null,
            user_id_str: $data['legacy']['user_id_str'],
            reply_to_id_str: $data['legacy']['in_reply_to_status_id_str'] ?? null,
            quoted_tweet: $quoted_tweet,
            retweet: $retweet,
            author: $author,
            media: $mediaCollection,
            links: $links,
        );
    }
}
