<?php

namespace App\Domain\Twitter\DTO;

use Carbon\Carbon;

class TweetDTO
{
    public function __construct(
        public string $rest_id,
        public string $conversation_id_str,
        public Carbon $created_at,
        public string $full_text,
        public bool $is_quote_status,
        public ?string $quoted_status_id_str,
        public string $user_id_str,
        public ?TweetDTO $quoted_tweet,
        public ?TweetDTO $retweet,
        public ?UserDTO $author,
    )
    {
    }

    public static function fromTweetResult(array $data): self
    {
        $quoted_tweet = null;
        if(!empty($data['result']['quoted_status_result'])) {
            $quoted_tweet = TweetDTO::fromTweetResult($data['result']['quoted_status_result']);
        }

        $retweet = null;
        if(!empty($data['result']['legacy']['retweeted_status_result'])) {
            $retweet = TweetDTO::fromTweetResult($data['result']['legacy']['retweeted_status_result']);
        }

        $author = null;
        if(!empty($data['result']['core']['user_result'])) {
            $author = UserDTO::fromUserResult($data['result']['core']['user_result']);
        }

        return new self(
            rest_id: $data['result']['rest_id'],
            conversation_id_str: $data['result']['legacy']['conversation_id_str'],
            created_at: Carbon::parse($data['result']['legacy']['created_at']),
            full_text: $data['result']['note_tweet']['note_tweet_results']['result']['text'] ?? $data['result']['legacy']['full_text'],
            is_quote_status: $data['result']['legacy']['is_quote_status'],
            quoted_status_id_str: $data['result']['legacy']['quoted_status_id_str'] ?? null,
            user_id_str: $data['result']['legacy']['user_id_str'],
            quoted_tweet: $quoted_tweet,
            retweet: $retweet,
            author: $author,
        );
    }


}
