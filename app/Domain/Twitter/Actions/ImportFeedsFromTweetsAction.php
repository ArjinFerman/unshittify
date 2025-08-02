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

class ImportFeedsFromTweetsAction extends BaseAction
{
    /**
     * @param Collection<TweetDTO> $tweets
     * @param string $path
     * @return Collection<Entry>
     * @throws \Throwable
     */
    public function execute(Collection $tweets, string $path = ''): Collection
    {
        return $this->optionalTransaction(function() use ($tweets, $path) {
            $existingAuthors = Author::query()->whereIn('name', $tweets->pluck('author.name'))->get();
            $newAuthorsTweets = $tweets->whereNotIn('author.name', $existingAuthors->pluck('name'));
            $newAuthors = [];
            foreach ($newAuthorsTweets as $tweet) {
                $newAuthors[] = [
                    'name' => $tweet->author->name,
                    'description' => $tweet->author->description,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            Author::query()->insert($newAuthors);
            $addedAuthors = Author::query()->whereIn('name', $newAuthorsTweets->pluck('author.name'))->get();

            if (count($newAuthors) != $addedAuthors->count()) {
                Log::warning("New Author count does not match added Author count" . count($newAuthors) . " vs {$addedAuthors->count()}");
            }

            $authors = $existingAuthors->merge($addedAuthors)->keyBy('name');

            $existingFeedsUrls = $tweets->map(function (TweetDTO $tweet) {
                return config('twitter.base_url') . $tweet->author->screen_name;
            });

            $existingFeeds = Feed::query()
                ->whereIn('url', $existingFeedsUrls)
                ->whereType(FeedType::TWITTER)
                ->get();

            $newFeeds = [];
            foreach ($tweets as $tweet) {
                if ($existingFeeds->contains('url', config('twitter.base_url') . $tweet->author->screen_name))
                    continue;

                $author = $authors->get($tweet->author->name);

                $url = config('twitter.base_url') . $tweet->author->screen_name;
                $newFeeds[$url] = [
                    'name' => $tweet->author->screen_name,
                    'url' => $url,
                    'author_id' => $author->id,
                    'type' => FeedType::TWITTER,
                    'status' => FeedStatus::PREVIEW,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            Feed::query()->insert(array_values($newFeeds));
            $feeds = Feed::query()->whereIn('url', $existingFeedsUrls)->get()
                ->merge($existingFeeds)->keyBy('name');

            $result = new Collection();
            foreach ($tweets as $tweet) {
                $author = $authors->get($tweet->author->name);
                $media = [
                    [
                        'media_object' => [
                            'media_object_id' => "twitter-profile-{$tweet->author->rest_id}",
                            'type' => MediaType::IMAGE,
                            'url' => $tweet->author->profile_image_url_https,
                            'content_type' => mimeType($tweet->author->profile_image_url_https),
                            'quality' => 1,
                            'properties' => null,
                        ],
                        'mediable' => [
                            'purpose' => MediaPurpose::AVATAR,
                            'mediable_type' => Author::class,
                            'mediable_id' => $author->id,
                        ],
                    ],
                ];

                foreach ($tweet->media as $mediaItem) {
                    $media[] = [
                        'media_object' => [
                            'media_object_id' => $mediaItem->media_object_id,
                            'type' => $mediaItem->type,
                            'url' => $mediaItem->url,
                            'content_type' => $mediaItem->content_type,
                            'quality' => $mediaItem->quality,
                            'properties' => json_encode($mediaItem->properties),
                        ],
                        'mediable' => [
                            'purpose' => MediaPurpose::CONTENT
                        ],
                    ];
                }

                $result->put($tweet->rest_id, [
                    'tweet' => $tweet,
                    'author' => $author,
                    'feed' => $feeds->get($tweet->author->screen_name),
                    'media' => $media,
                ]);
            }

            return $result;
        });
    }
}
