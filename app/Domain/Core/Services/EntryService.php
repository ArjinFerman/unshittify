<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Enums\CoreTagType;
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

class EntryService
{
    public function getSubscribedFeedEntriesUnread()
    {
        return $this->getEntriesForView(function (EntryQueryBuilder $query) {
            return $query->where('core_feeds.status', FeedStatus::ACTIVE->value)
                ->whereIsRead(false);
        }, function (EntryQueryBuilder $query) {
            return $query->where(EntryQueryBuilder::RECUSRIVE_REF_TABLE . '.ref_type', '!=', ReferenceType::REPLY_TO->value);
        });
    }

    public function getUnreadCount(): int
    {
        return Entry::query()
            ->whereHas('feed', function ($query) {
                $query->whereStatus(FeedStatus::ACTIVE);
            })
            ->whereIsRead(false)
            ->count();
    }

    public function getStarredEntries()
    {
        return $this->getEntriesForView(function (EntryQueryBuilder $query) {
            return $query->whereHas('tags', function ($tagQuery) {
                $tagQuery->where('core_tags.id', CoreTagType::STARRED);
            })
                ->orderBy('core_entries.published_at', 'desc')
                ->limit(10);
        }, function (EntryQueryBuilder $query) {
            return $query->whereRaw('0 = 1');
        });
    }

    public function getStarredCount()
    {
        return Entry::query()
            ->whereHas('tags', function ($query) {
                $query->where('core_tags.id', CoreTagType::STARRED);
            })->count();
    }

    public function getSubscribedFeedEntries()
    {
        return $this->getEntriesForView(function (EntryQueryBuilder $query) {
            return $query->where('core_feeds.status', FeedStatus::ACTIVE->value);
        }, function (EntryQueryBuilder $query) {
            return $query->where(EntryQueryBuilder::RECUSRIVE_REF_TABLE . '.ref_type', '!=', ReferenceType::REPLY_TO->value);
        });
    }

    public function getFeedEntries(int $id, Carbon|string|null $after = null)
    {
        return $this->getEntriesForView(function (EntryQueryBuilder $query) use ($id, $after) {
            if ($after)
                $query->where('published_at', '<=', $after);

            return $query->whereFeedId($id);
        }, function (EntryQueryBuilder $query) {
            return $query->where(EntryQueryBuilder::RECUSRIVE_REF_TABLE . '.ref_type', '!=', ReferenceType::REPLY_TO->value);
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
                            $sub->where(EntryQueryBuilder::RECUSRIVE_REF_TABLE . '.ref_type', '!=', ReferenceType::REPLY_TO->value);
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
        $entries = $entryQuery(Entry::query()->withViewData());

        if (!$entries->getQuery()->orders)
            $entries->orderBy('core_entries.published_at', 'asc');

        if (!$entries->getQuery()->limit)
            $entries->limit(10);

        $entries = $entries->get();

        $references = Entry::query()->withRecursiveReferences($entries->pluck('id'));

        /** @var Collection $entries */
        $references = $references
            ->withViewData()
            ->orderBy(EntryQueryBuilder::RECUSRIVE_REF_TABLE.'.ref_path')
        ;

        if ($referenceQuery)
            $references = $referenceQuery($references, $entries);

        $references->addSelect([
            EntryQueryBuilder::RECUSRIVE_REF_TABLE . '.ref_path',
            EntryQueryBuilder::RECUSRIVE_REF_TABLE . '.ref_type',
        ]);

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
