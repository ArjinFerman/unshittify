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
        return $this->getEntriesForView(function ($query) {
            return $query
                ->whereHas('feed', function ($feedQuery) {
                    $feedQuery->where('status', '=', FeedStatus::ACTIVE->value);
                })
                ->orderBy('entries.published_at', 'asc')
                ->whereIsRead(false)
                ->limit(10);
        }, function ($query) {
            $query->where('ref_type', '!=', ReferenceType::REPLY_FROM->value);
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
            return $query->whereIsStarred(true)
                ->orderBy('entries.published_at', 'desc')
                ->limit(10);
        }, function ($query) {
            return $query->whereRaw('0 = 1');
        });
    }

    public function getStarredCount(): int
    {
        return Entry::query()->whereIsStarred(true)->count();
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
        $entries = Entry::entriesWithReferences($entryQuery, $referenceQuery)
            ->with([
                'feed',
                'media',
                'tags',
            ]);

        $entries = $entries->get()->toTree('references');
        return new EntryCollectionDTO(EntryDTO::collect($entries));
    }
}
