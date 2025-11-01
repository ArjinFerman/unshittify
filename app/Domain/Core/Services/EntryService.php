<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\DTO\EntryCollectionDTO;
use App\Domain\Core\DTO\EntryDTO;
use App\Domain\Core\Enums\CoreTagType;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry;
use App\Support\CompositeId;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class EntryService
{
    public function getSubscribedFeedEntriesUnread(): EntryCollectionDTO
    {
        return $this->getEntriesForView(function (Builder $query) {
            return $query->whereHas('feed', function (Builder $feedQuery) {
                $feedQuery->where('status', '=', FeedStatus::ACTIVE->value);
            })->whereIsRead(false);
        }, function ($query) {
            return $query->where('ref_type', '!=', ReferenceType::REPLY_FROM->value);
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

    public function getStarredEntries(): EntryCollectionDTO
    {
        return $this->getEntriesForView(function (Builder $query) {
            return $query->whereHas('tags', function ($tagQuery) {
                $tagQuery->where('tags.id', CoreTagType::STARRED);
            })
                ->orderBy('entries.published_at', 'desc')
                ->limit(10);
        }, function ($query) {
            return $query->whereRaw('0 = 1');
        });
    }

    public function getStarredCount(): int
    {
        return Entry::query()
            ->whereHas('tags', function ($query) {
                $query->where('tags.id', CoreTagType::STARRED);
            })->count();
    }

    public function getSubscribedFeedEntries(): EntryCollectionDTO
    {
        return $this->getEntriesForView(function (Builder $query) {
            return $query->whereHas('feed', function (Builder $feedQuery) {
                $feedQuery->where('status', '=', FeedStatus::ACTIVE->value);
            });
        }, function ($query) {
            return $query->where('ref_type', '!=', ReferenceType::REPLY_FROM->value);
        });
    }

    public function getFeedEntries(int $id, Carbon|string|null $after = null): EntryCollectionDTO
    {
        return $this->getEntriesForView(function (Builder $query) use ($id, $after) {
            if ($after)
                $query->where('published_at', '<=', $after);

            return $query->whereFeedId($id);
        }, function ($query) {
            return $query->where('ref_type', '!=', ReferenceType::REPLY_FROM->value);
        });
    }

    public function getEntryWithReplies(CompositeId $entryId, Carbon|string|null $after = null): EntryCollectionDTO
    {
        return $this->getEntriesForView(function (Builder $query) use ($entryId, $after) {
            return $query->where('composite_id', $entryId);
        });
    }

    /**
     * Get a list of entries prepared for rendering in a View, with references, media, and tags fetched through optimized queries
     *
     * @param callable(Builder): Builder $entryQuery
     * @param callable(Builder): Builder|null $referenceQuery
     * @return EntryCollectionDTO
     */
    protected function getEntriesForView(callable $entryQuery, callable $referenceQuery = null): EntryCollectionDTO
    {
        $entries = Entry::query()
            ->with([
                'feed',
                'media',
                'tags',
                'references' => $referenceQuery,
                'references.feed',
                'references.media',
                'references.tags'
            ])
            ->where($entryQuery);

        if (!$entries->getQuery()->orders)
            $entries->orderBy('entries.published_at', 'asc');

        if (!$entries->getQuery()->limit)
            $entries->limit(10);

        return new EntryCollectionDTO(EntryDTO::collect($entries->get()));
    }
}
