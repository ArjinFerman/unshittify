<?php

namespace App\Domain\Twitter\Actions;

use App\Domain\Core\Actions\BaseAction;
use App\Domain\Core\Actions\FindOrCreateAuthorAction;
use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry;
use App\Domain\Twitter\DTO\TweetDTO;
use App\Domain\Twitter\Models\Tweet;
use App\Domain\Twitter\Models\User;

class ImportTweetAction extends BaseAction
{
    /**
     * @throws \Throwable
     */
    public function execute(TweetDTO $tweetData): Entry
    {
        return $this->optionalTransaction(function () use ($tweetData) {
            $twitterUser = $this->findOrCreateAuthorUser($tweetData);
            $tweet = $this->findOrCreateTweet($tweetData, $twitterUser);

            $entry = $tweet->entry;
            if (!$entry) {
                $title = "@{$tweetData->author->screen_name}";
                $content = $tweetData->full_text;
                /** @var Entry $reference */
                $reference = null;
                $referenceType = null;

                if ($tweetData->retweet) {
                    $content = null;
                    $reference = self::make()->withoutTransaction()->execute($tweetData->retweet);
                    $referenceType = ReferenceType::REPOST;
                } else if ($tweetData->quoted_tweet) {
                    $reference = self::make()->withoutTransaction()->execute($tweetData->quoted_tweet);
                    $referenceType = ReferenceType::QUOTE;
                }

                /** @var Entry $entry */
                $entry = Entry::create([
                    'author_id' => $twitterUser->author_id,
                    'entryable_id' => $tweet->id,
                    'entryable_type' => Tweet::class,
                    'url' => config('twitter.status_base_url') . $tweetData->author->rest_id,
                    'title' => $title,
                    'content' => $content,
                ]);

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
                'reply_to_id' => null,
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

            $twitterUser = User::create([
                'author_id' => $author->id,
                'screen_name' => $tweetData->author->screen_name,
                'twitter_user_id' => $tweetData->author->rest_id,
            ]);
        }

        return $twitterUser;
    }
}
