<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Enums\MediaPurpose;
use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\Media;
use App\Domain\Core\Models\Tag;
use App\Domain\Core\QueryBuilders\EntryQueryBuilder;
use App\View\Data\EntryViewDataCollection;
use Illuminate\Support\Collection;

class FeedService
{
    public function getSubscribedFeedEntries()
    {
        return $this->getEntriesForView(function (EntryQueryBuilder $query) {
            return $query->where('core_feeds.status', FeedStatus::ACTIVE->value);
        }, function (EntryQueryBuilder $query) {
            return $query->where('core_entry_references.ref_type', '!=', ReferenceType::REPLY_TO->value);
        });
    }

    public function getFeed($id)
    {
        return $this->getEntriesForView(function (EntryQueryBuilder $query) use ($id) {
            return $query->whereFeedId($id);
        }, function (EntryQueryBuilder $query) {
            return $query->where('core_entry_references.ref_type', '!=', ReferenceType::REPLY_TO->value);
        });
    }

    public function getTweetWithReplies($tweetId)
    {
        return $this->getEntriesForView(function (EntryQueryBuilder $query) use ($tweetId) {
            return $query->whereJsonContains('metadata->tweet_id', $tweetId);
        });
    }

    /**
     * Get a list of entries prepared for rendering in a View, with references, media, and tags fetched through optimized queries
     *
     * @param callable(EntryQueryBuilder): EntryQueryBuilder $entryQuery
     * @param callable(EntryQueryBuilder): EntryQueryBuilder|null $referenceQuery
     * @return EntryViewDataCollection
     */
    protected function getEntriesForView(callable $entryQuery, callable $referenceQuery = null): EntryViewDataCollection
    {
        $entries = Entry::query()
            ->withViewData()
            ->orderBy('core_entries.published_at', 'desc')
            ->limit(10)
        ;

        $entries = $entryQuery($entries);
        $entries = $entries->get();

        /** @var Collection $entries */
        $references = Entry::query()
            ->withViewData()
            ->withReferenceData()
            ->whereReferencesOf($entries)
            ->orderBy('core_entry_references.ref_path')
        ;

        if ($referenceQuery)
            $references = $referenceQuery($references);

        $references = $references->get();

        $entryIds = $entries->pluck('id')->merge($references->pluck('id'));
        $media = Media::query()
            ->join('core_mediables', 'core_media.id', '=', 'core_mediables.media_id', 'left')
            ->whereIn('core_mediables.mediable_id', $entryIds)
            ->where('core_mediables.mediable_type', Entry::class)
            ->where('core_mediables.purpose', MediaPurpose::CONTENT->value)
            ->get()
        ;

        $tags = Tag::query()
            ->join('core_taggables', 'core_tags.id', '=', 'core_taggables.tag_id', 'left')
            ->whereIn('core_taggables.taggable_id', $entryIds)
            ->where('core_taggables.taggable_type', Entry::class)
            ->get()
        ;

        $entries = new EntryViewDataCollection($entries);
        $entries->addEntries($references);
        $media->each(function (Media $mediaItem) use ($entries) { $entries->addMedia($mediaItem); });
        $tags->each(function (Tag $tag) use ($entries) { $entries->addTag($tag); });

        return $entries;
    }
}
