<?php

namespace App\Domain\Twitter\Actions;

use App\Domain\Core\Actions\BaseAction;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Enums\FeedType;
use App\Domain\Core\Enums\MediaPurpose;
use App\Domain\Core\Enums\MediaType;
use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Author;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\EntryReference;
use App\Domain\Core\Models\Feed;
use App\Domain\Core\Models\Media;
use App\Domain\Core\Models\Mediable;
use App\Domain\Twitter\DTO\TweetDTO;
use App\Domain\Twitter\Models\Tweet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ImportTweetsAction extends BaseAction
{
    /**
     * @param Collection<TweetDTO> $tweets
     * @param bool $createFeed
     * @param string $path
     * @return Collection<Entry>
     * @throws \Throwable
     */
    public function execute(Collection $tweets, bool $createFeed = false, string $path = ''): Collection
    {
        return $this->optionalTransaction(function () use ($tweets, $createFeed, $path) {
            $references = new Collection();
            $keyedTweets = $tweets->keyBy('rest_id');

            foreach ($tweets as $tweet) {
                $this->processReferences($keyedTweets, $references, $tweet);
            }

            $tweetAuthorsFeeds = ImportFeedsFromTweetsAction::make()->withoutTransaction()->execute($keyedTweets);
            $existingTweets = Tweet::whereIn('metadata->tweet_id', $tweetAuthorsFeeds->pluck('tweet.rest_id'))
                ->get()->keyBy('metadata.tweet_id');
            $newTweets = $tweetAuthorsFeeds->whereNotIn('tweet.rest_id', $existingTweets->pluck('metadata.tweet_id'));

            $addedTweets = [];
            foreach ($newTweets as $newTweetData) {
                $addedTweets[] = [
                    'type'              => Tweet::class,
                    'feed_id'           => $newTweetData['feed']->id,
                    'url'               => $newTweetData['tweet']->getTweetUrl(),
                    'title'             => "@{$newTweetData['tweet']->author->screen_name}",
                    'content'           => $newTweetData['tweet']->full_text,
                    'published_at'      => $newTweetData['tweet']->created_at,
                    'metadata'          => json_encode($this->getMetadata($newTweetData['tweet'])),
                    'created_at'        => Carbon::now(),
                    'updated_at'        => Carbon::now(),
                ];
            }

            Tweet::query()->insert($addedTweets);
            $addedTweets = Tweet::whereIn('metadata->tweet_id', $newTweets->pluck('tweet.rest_id'))->get();

            if ($newTweets->count() != $addedTweets->count()) {
                Log::warning("New Tweet count does not match added Tweet count {$newTweets->count()} vs {$addedTweets->count()}");
            }

            /** @var Collection<Tweet> $allTweets */
            $allTweets = $existingTweets->merge($addedTweets)->keyBy('metadata.tweet_id');

            $invalidReferences = new Collection();
            foreach ($references as $restId => $reference) {
                $this->fixReplyPath($references, $allTweets, $restId, $invalidReferences);

                $reference['created_at'] = Carbon::now();
                $reference['updated_at'] = Carbon::now();
            }

            $references->forget($invalidReferences);

            $existingReferences = EntryReference::query()
                ->whereIn('entry_id', $allTweets->pluck('id'))
                ->orWhereIn('ref_entry_id', $allTweets->pluck('id'))
                ->get()->keyBy('ref_path');

            $newReferences = $references->whereNotIn('ref_path', $existingReferences->pluck('ref_path'));
            EntryReference::query()->insert($newReferences->toArray());

            $media = new Collection();
            $mediables = new Collection();
            foreach ($tweetAuthorsFeeds as $tweetData)
            {
                foreach ($tweetData['media'] as $mediaItem) {
                    $mediaItem['created_at'] = Carbon::now();
                    $mediaItem['updated_at'] = Carbon::now();
                    $media->add($mediaItem['media_object']);

                    if ($mediaItem['mediable']['purpose'] == MediaPurpose::CONTENT) {
                        $mediaItem['mediable']['mediable_type'] = Tweet::class;
                        $mediaItem['mediable']['mediable_id'] = $allTweets->get($tweetData['tweet']->rest_id)->id;
                    }

                    $mediaItem['mediable']['media_object_id'] = $mediaItem['media_object']['media_object_id'];
                    // FIXME: HACK: Add by key to ensure no duplicates
                    $mediables->put("{$mediaItem['mediable']['mediable_id']};{$mediaItem['mediable']['media_object_id']};{$mediaItem['mediable']['mediable_type']}", $mediaItem['mediable']);
                }
            }

            $existingMedia = Media::query()
                ->whereIn('media_object_id', $media->pluck('media_object_id'))
                ->get();
            $existingMediables = Mediable::query()
                ->where(function (Builder $query) use ($mediables) {
                    $query->where('mediable_type', Tweet::class);
                    $query->whereIn('mediable_id', $mediables->where('mediable_type', Tweet::class)->pluck('mediable_id'));
                })->orWhere(function (Builder $query) use ($mediables) {
                    $query->where('mediable_type', Author::class);
                    $query->whereIn('mediable_id', $mediables->where('mediable_type', Author::class)->pluck('mediable_id'));
                })
                ->get();

            Media::query()->upsert($media->unique()->toArray(), ['media_object_id', 'quality']);
            $allMedia = Media::query()->whereIn('media_object_id', $media->pluck('media_object_id'))->get()->keyBy('media_object_id');

            foreach ($mediables as $key => $mediable) {
                $mediable['media_id'] = $allMedia->get($mediable['media_object_id'])->id;
                unset($mediable['media_object_id']);

                $mediable['created_at'] = Carbon::now();
                $mediable['updated_at'] = Carbon::now();
                $mediables->put($key, $mediable);
            }

            Mediable::query()->insert($mediables->whereNotIn('mediable_id', $existingMediables->pluck('mediable_id'))->unique()->toArray());

            return $allTweets;
        });
    }

    /**
     * @param Collection<array> $references
     * @param Collection<Tweet> $dbTweets
     * @param string $restId
     * @return void
     */
    protected function fixReplyPath(Collection $references, Collection $dbTweets, string $restId, Collection $invalidReferences): void
    {
        $reference = $references[$restId] ?? null;
        if (!$reference || !is_string($reference['entry_id']))// isset($reference['is_fixed']))
            return;

        $path = array_filter(explode('/', $reference['ref_path']));

        $fixedPath = $path;
        foreach ($fixedPath as &$pathRestId) {
            if ($pathRestId) {
                if (!$dbTweets->get($pathRestId)) {
                    $invalidReferences->add($restId);
                    return;
                }
                $pathRestId = $dbTweets->get($pathRestId)->id;
            }
        }

        if ($reference['ref_type'] == ReferenceType::REPLY_TO && $replyTo = ($references[$path[0]] ?? null)) {
            if ($replyTo['ref_type'] == ReferenceType::REPLY_TO) {
                $this->fixReplyPath($references, $dbTweets, $path[0], $invalidReferences);
                $fixedPath = array_unique(array_filter(array_merge(explode('/', $replyTo['ref_path']), $fixedPath)));
            }
        }

        $reference['entry_id'] = $dbTweets->get($reference['entry_id'])->id;
        $reference['ref_entry_id'] = $dbTweets->get($reference['ref_entry_id'])->id;
        $reference['ref_path'] = '/' . implode('/', $fixedPath) . '/';
        $references[$restId] = $reference;
    }

    protected function processReferences(Collection $resultTweets, Collection $references, TweetDTO $tweet, string $path = ''): void
    {
        /** @var Entry $reference */
        $path = $path ? "$path/{$tweet->rest_id}" : $tweet->rest_id;

        if ($tweet->retweet) {
            $resultTweets->put($tweet->retweet->rest_id, $tweet->retweet);
            $references->put($tweet->rest_id, [
                'entry_id'      => $tweet->rest_id,
                'ref_entry_id'  => $tweet->retweet->rest_id,
                'ref_type'      => ReferenceType::REPOST,
                'ref_path'      => "$path/{$tweet->retweet->rest_id}",
            ]);
            $this->processReferences($resultTweets, $references, $tweet->retweet, $path);
        } else if ($tweet->quoted_tweet) {
            $resultTweets->put($tweet->quoted_tweet->rest_id, $tweet->quoted_tweet);
            $references->put($tweet->rest_id, [
                'entry_id'      => $tweet->rest_id,
                'ref_entry_id'  => $tweet->quoted_tweet->rest_id,
                'ref_type'      => ReferenceType::QUOTE,
                'ref_path'      => "$path/{$tweet->quoted_tweet->rest_id}",
            ]);
            $this->processReferences($resultTweets, $references, $tweet->quoted_tweet, $path);
        } else if ($tweet->reply_to_id_str) {
            $path = "$tweet->reply_to_id_str/{$tweet->rest_id}";
            $references->put($tweet->rest_id, [
                'entry_id'      => $tweet->reply_to_id_str,
                'ref_entry_id'  => $tweet->rest_id,
                'ref_type'      => ReferenceType::REPLY_TO,
                'ref_path'      => $path,
            ]);
        }
    }

    protected function getMetadata(TweetDTO $tweetData): array
    {
        return [
            'twitter_user_id' => $tweetData->author->rest_id,
            'tweet_id' => $tweetData->rest_id,
            'retweet_id' => $tweetData->retweet?->rest_id,
            'quoted_tweet_id' => $tweetData->quoted_tweet?->rest_id,
            'reply_to_id' => $tweetData->reply_to_id_str,
            'conversation_id' => $tweetData->conversation_id_str,
            'user' => [
                'screen_name' => $tweetData->author->screen_name,
            ]
        ];
    }
}
