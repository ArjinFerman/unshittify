<?php

namespace App\Domain\Legacy\V1\DTO;


use App\Domain\Core\DTO\BaseDTO;
use App\Domain\Core\DTO\EntryCollectionDTO;
use App\Domain\Core\DTO\EntryDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Support\CompositeId;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use stdClass;

;

class LegacyEntryCollectionDTO extends EntryCollectionDTO
{
    /**
     * @param Collection<int, stdClass> $v1Entries
     * @param Collection<int, stdClass> $v1Feeds
     * @param Collection<int, stdClass> $v1FeedAuthors
     * @param Collection<int, stdClass> $v1FeedAvatars
     * @param Collection<int, stdClass> $v1EntryMedia
     * @param Collection<int, stdClass> $v1EntryTags
     * @param Collection<int, stdClass> $v1EntryReferences
     * @return self
     */
    public static function createFromRawDB(
        Collection $v1Entries,
        Collection $v1Feeds,
        Collection $v1FeedAuthors,
        Collection $v1FeedAvatars,
        Collection $v1EntryMedia,
        Collection $v1EntryTags,
        Collection $v1EntryReferences,
    ): self
    {
        $v1Feeds = $v1Feeds->keyBy('id');
        $v1FeedAuthors = $v1FeedAuthors->keyBy('id');
        $v1FeedAvatars = $v1FeedAvatars->keyBy('mediable_id');
        $v1EntryMedia = $v1EntryMedia->groupBy('mediable_id');
        $v1EntryTags = $v1EntryTags->groupBy('taggable_id');
        $v1EntryReferences = $v1EntryReferences->groupBy('entry_id');

        $entries = new Collection();
        foreach ($v1Entries as $v1Entry) {
            $v1Feed = $v1Feeds->get($v1Entry->feed_id);
            $v1Entry->metadata = json_decode($v1Entry->metadata);

            $entryFeed = LegacyFeedDTO::createFromRawDB(
                $v1FeedAuthors->get($v1Feed->author_id),
                $v1FeedAvatars->get($v1Feed->author_id),
                $v1Feed,
                $v1Entry
            );

            $content = $v1Entry->content;

            $entryMedia = LegacyMediaDTO::collectFromRawDB($v1Entry, $v1EntryMedia, $content);
            $entryReferences = LegacyEntryReferenceDTO::collectFromRawDB($v1Entry, $v1EntryReferences, $content);

            $metadata = (array)$v1Entry->metadata;
            $metadata['conversation_id_str'] = $metadata['conversation_id'];
            $metadata['quoted_status_id_str'] = $metadata['quoted_tweet_id'];
            $metadata['reply_to_id_str'] = $metadata['reply_to_id'];
            $metadata['retweeted_status_result'] = $metadata['retweet_id'];

            unset($metadata['conversation_id']);
            unset($metadata['quoted_tweet_id']);
            unset($metadata['reply_to_id']);
            unset($metadata['retweet_id']);

            $tags = $v1EntryTags->get($v1Entry->id);
            $entries->add(new EntryDTO(
                composite_id: CompositeId::create(ExternalSourceType::TWITTER, $v1Entry->metadata->tweet_id),
                feed_composite_id: $entryFeed->composite_id,
                url: $v1Entry->url,
                title: "@{$entryFeed->name}",
                content: $content,
                published_at: Carbon::parse($v1Entry->published_at),
                is_read: $v1Entry->is_read,
                is_starred: $tags->where('name', 'STARRED')->count() > 0,
                metadata: $metadata,
                feed: $entryFeed,
                references: $entryReferences,
                media: $entryMedia,
                tags: new Collection()
            ));
        }

        return new self($entries);
    }
}
