<?php

namespace App\Domain\Twitter\Actions;

use App\Domain\Core\Actions\BaseAction;
use App\Domain\Core\Actions\FindOrCreateAuthorAction;
use App\Domain\Core\Actions\FindOrCreateFeedAction;
use App\Domain\Core\DTO\MediaDTO;
use App\Domain\Core\Enums\FeedType;
use App\Domain\Core\Enums\MediaPurpose;
use App\Domain\Core\Enums\MediaType;
use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\Feed;
use App\Domain\Twitter\DTO\TweetDTO;
use App\Domain\Twitter\Models\Tweet;
use App\Domain\Twitter\Models\User;

class ImportTweetAction extends BaseAction
{
    /**
     * @throws \Throwable
     */
    public function execute(TweetDTO $tweetData, bool $createFeed = false, string $path = ''): Entry
    {
        return $this->optionalTransaction(function () use ($tweetData, $createFeed, $path) {
            $twitterUser = $this->findOrCreateAuthorUser($tweetData);
            $url = config('twitter.base_url') . "{$twitterUser->screen_name}/status/{$tweetData->rest_id}";

            $tweet = Tweet::whereUrl($url)->first();

            if ($tweet)
                return $tweet;

            $tweet = new Tweet;
            $tweet->type = Tweet::class;
            $tweet->url = $url;
            $tweet->title = "@{$tweetData->author->screen_name}";
            $tweet->content = $tweetData->full_text;
            $tweet->published_at = $tweetData->created_at;
            $tweet->metadata = $this->getMetadata($tweetData, $twitterUser);

            $feed = FindOrCreateFeedAction::make()->withoutTransaction()->execute(
                config('twitter.base_url') . $twitterUser->screen_name,
                FeedType::TWITTER,
                $twitterUser->author,
                $twitterUser->screen_name
            );

            $tweet->feed()->associate($feed);

            foreach ($tweetData->links as $link) {
                FindOrImportLinkAction::make()->execute($link);
            }

            $tweet->save();

            /** @var MediaDTO $mediaItem */
            foreach ($tweetData->media as $mediaItem) {
                $tweet->media()->create([
                    'media_object_id' => $mediaItem->media_object_id,
                    'type' => $mediaItem->type,
                    'url' => $mediaItem->url,
                    'content_type' => $mediaItem->content_type,
                    'quality' => $mediaItem->quality,
                    'properties' => $mediaItem->properties,
                ], [
                    'purpose' => MediaPurpose::CONTENT
                ]);
            }

            /** @var Entry $reference */
            $path = "$path/{$tweet->id}";
            $reference = null;
            $referenceType = null;
            if ($tweetData->retweet) {
                $tweet->content = null;
                $reference = self::make()->withoutTransaction()->execute($tweetData->retweet, false, $path);
                $referenceType = ReferenceType::REPOST;
            } else if ($tweetData->quoted_tweet) {
                $reference = self::make()->withoutTransaction()->execute($tweetData->quoted_tweet, false, $path);
                $referenceType = ReferenceType::QUOTE;
            } else if ($tweetData->reply_to_id_str) {
                $reference = Tweet::whereTweetId($tweetData->reply_to_id_str)->first()?->entry;
                $referenceType = ($reference ? ReferenceType::REPLY_TO : null);
            }

            if ($reference) {
                $path = "$path/{$reference->id}/";
                $tweet->references()->attach($reference->id, ['ref_type' => $referenceType, 'ref_path' => $path]);
            }

            return $tweet;
        });
    }

    protected function getMetadata(TweetDTO $tweetData, User $twitterUser): array
    {
        return [
            'twitter_user_id' => $twitterUser->id,
            'tweet_id' => $tweetData->rest_id,
            'retweet_id' => $tweetData->retweet?->rest_id,
            'quoted_tweet_id' => $tweetData->quoted_tweet?->rest_id,
            'reply_to_id' => $tweetData->reply_to_id_str,
            'conversation_id' => $tweetData->conversation_id_str,
            'user' => [
                'screen_name' => $twitterUser->screen_name,
            ]
        ];
    }

    protected function findOrCreateAuthorUser(TweetDTO $tweetData): User
    {
        $twitterUser = User::whereTwitterUserId($tweetData->author->rest_id)->first();
        if (!$twitterUser) {
            $author = FindOrCreateAuthorAction::make()->withoutTransaction()->execute($tweetData->author->name, [
                'description' => $tweetData->author->description,
            ]);

            $author->media()->create([
                'media_object_id' => "twitter-profile-{$tweetData->author->rest_id}",
                'type' => MediaType::IMAGE,
                'url' => $tweetData->author->profile_image_url_https,
                'content_type' => mimeType($tweetData->author->profile_image_url_https),
                'quality' => 1,
            ], [
                'purpose' => MediaPurpose::AVATAR
            ]);

            $twitterUser = User::create([
                'author_id' => $author->id,
                'screen_name' => $tweetData->author->screen_name,
                'twitter_user_id' => $tweetData->author->rest_id,
            ]);
        }

        return $twitterUser;
    }
}
