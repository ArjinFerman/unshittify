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
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FeedService
{
    public function getSubscribedFeedEntriesUnread()
    {
        return $this->getEntriesForView(function (EntryQueryBuilder $query) {
            return $query->where('core_feeds.status', FeedStatus::ACTIVE->value)
                ->whereNotExists(function (Builder $tagQuery) {
                    $tagQuery->from('core_taggables')
                        ->join('core_tags', 'core_tags.id', '=', 'core_taggables.tag_id')
                        ->where('taggable_type', 'App\Domain\Core\Models\Entry')
                        ->whereColumn('taggable_id', '=', 'core_entries.id');
                });
        }, function (EntryQueryBuilder $query) {
            return $query->where('core_entry_references.ref_type', '!=', ReferenceType::REPLY_TO->value);
        });
    }

    public function getSubscribedFeedEntries()
    {
        return $this->getEntriesForView(function (EntryQueryBuilder $query) {
            return $query->where('core_feeds.status', FeedStatus::ACTIVE->value);
        }, function (EntryQueryBuilder $query) {
            return $query->where('core_entry_references.ref_type', '!=', ReferenceType::REPLY_TO->value);
        });
    }

    public function getFeed(int $id, Carbon|string|null $after = null)
    {
        return $this->getEntriesForView(function (EntryQueryBuilder $query) use ($id, $after) {
            if ($after)
                $query->where('published_at', '<=', $after);

            return $query->whereFeedId($id);
        }, function (EntryQueryBuilder $query) {
            return $query->where('core_entry_references.ref_type', '!=', ReferenceType::REPLY_TO->value);
        });
    }

    public function getTweetWithReplies(string $tweetId, Carbon|string|null $after = null)
    {
        return $this->getEntriesForView(function (EntryQueryBuilder $query) use ($tweetId) {
            return $query->where('metadata->tweet_id', $tweetId);
        }, function (EntryQueryBuilder $query, Collection $entries) use ($after) {
            if ($entries->count() != 1)
                Log::warning("getTweetWithReplies should have only one main entry (count: {$entries->count()})}");

            $entryId = $entries->first()->id;

            if ($after)
                $query->where('published_at', '<=', $after);

            return $query
                ->leftJoin('core_entry_references as ref_to_parent', function(JoinClause $join) use ($entryId) {
                    $join->on('ref_to_parent.entry_id', '=', 'core_entries.id');
                    $join->on('ref_to_parent.ref_entry_id', '=', $entryId);
                })
                ->leftJoin('core_entry_references as refs_of_parent', function(JoinClause $join) use ($entryId) {
                    $join->on('core_entry_references.entry_id', '=', 'refs_of_parent.entry_id');
                    $join->on('refs_of_parent.ref_entry_id', '=', $entryId);
                })
                ->orWhere(function ($or) use ($entryId, $after) {
                    if ($after)
                        $or->where('published_at', '<=', $after);

                    $or->where(function ($and) use ($entryId) {
                        $and->where(function ($sub) use ($entryId) {
                            $sub->where('ref_to_parent.ref_entry_id', $entryId);
                            $sub->where('ref_to_parent.ref_type', '=', ReferenceType::REPLY_TO->value);
                        })->orWhere(function ($sub) use ($entryId) {
                            $sub->where('refs_of_parent.ref_entry_id', $entryId);
                            $sub->where('core_entry_references.ref_type', '!=', ReferenceType::REPLY_TO->value);
                        });
                    });
                });
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
            $references = $referenceQuery($references, $entries);

        /** @var Collection<Entry> $entries */
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
