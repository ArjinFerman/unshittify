<?php

namespace App\Domain\Twitter\Actions;

use App\Domain\Core\Actions\BaseAction;
use App\Domain\Core\Actions\FindOrCreateAuthorAction;
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
    public function execute(TweetDTO $tweetData, bool $createFeed = false): Entry
    {
        return $this->optionalTransaction(function () use ($tweetData, $createFeed) {
            $twitterUser = $this->findOrCreateAuthorUser($tweetData);
            $tweet = $this->findOrCreateTweet($tweetData, $twitterUser);

            $entry = $tweet->entry;
            if (!$entry) {
                $entry = new Entry;
                $entry->url = config('twitter.status_base_url') . $tweetData->rest_id;
                $entry->title = "@{$tweetData->author->screen_name}";
                $entry->content = $tweetData->full_text;
                $entry->published_at = $tweetData->created_at;

                /** @var Entry $reference */
                $reference = null;
                $referenceType = null;
                if ($tweetData->retweet) {
                    $entry->content = null;
                    $reference = self::make()->withoutTransaction()->execute($tweetData->retweet);
                    $referenceType = ReferenceType::REPOST;
                } else if ($tweetData->quoted_tweet) {
                    $reference = self::make()->withoutTransaction()->execute($tweetData->quoted_tweet);
                    $referenceType = ReferenceType::QUOTE;
                } else if ($tweetData->reply_to_id_str) {
                    $reference = Tweet::whereTweetId($tweetData->reply_to_id_str)->first()?->entry;
                    $referenceType = ($reference ? ReferenceType::REPLY_TO : null);
                }

                $feed = $this->findOrCreateFeed($twitterUser, $createFeed);

                $entry->feed_id = $feed?->id;
                $entry->entryable()->associate($tweet);
                $entry->author()->associate($twitterUser->author);

                foreach ($tweetData->links as $link) {
                    FindOrImportLinkAction::make()->execute($link);
                }

                $entry->save();

                /** @var MediaDTO $mediaItem */
                foreach ($tweetData->media as $mediaItem) {
                    $entry->media()->create([
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

                if ($reference) {
                    $entry->references()->attach($reference->id, ['ref_type' => $referenceType]);
                }
            }

            return $entry;
        });
    }

    protected function findOrCreateTweet(TweetDTO $tweetData, User $twitterUser): Tweet
    {
        $tweet = Tweet::whereTweetId($tweetData->rest_id)->first() ??
            Tweet::create([
                'twitter_user_id' => $twitterUser->id,
                'tweet_id' => $tweetData->rest_id,
                'retweet_id' => $tweetData->retweet?->rest_id,
                'quoted_tweet_id' => $tweetData->quoted_tweet?->rest_id,
                'reply_to_id' => $tweetData->reply_to_id_str,
                'conversation_id' => $tweetData->conversation_id_str,
            ]);

        return $tweet;
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

    public function findOrCreateFeed(User $twitterUser, $createFeed = false): ?Feed
    {
        $feed = Feed::whereAuthorId($twitterUser->author_id)->first();
        if ($feed || !$createFeed)
            return $feed;

        $feed = new Feed;
        $feed->url = config('twitter.base_url') . $twitterUser->screen_name;
        $feed->author_id = $twitterUser->author_id;
        $feed->type = FeedType::TWITTER;
        $feed->name = $twitterUser->screen_name;
        $feed->save();

        return $feed;
    }
}
