<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\EntryReference;

class MarkAsReadAction extends BaseAction
{
    const MAX_LEVEL = 10;

    /**
     * @throws \Throwable
     */
    public function execute(array $entryIds = []): void
    {
        $this->optionalTransaction(function () use ($entryIds) {
            if (empty($entryIds)) {
                Entry::query()
                    ->whereHas('feed', function ($query) {
                        $query->whereStatus(FeedStatus::ACTIVE);
                    })
                    ->update(['is_read' => true]);
            } else {
                $this->markAsEntriesAsRead($entryIds);
            }
        });
    }

    protected function markAsEntriesAsRead(array $entryIds, int $level = 0): void
    {
        if ($level > self::MAX_LEVEL)
            return;

        Entry::query()
            ->whereIn('id', $entryIds)
            ->update(['is_read' => true]);

        $referenceIds = EntryReference::whereIn('entry_id', $entryIds)
            ->whereNot('ref_type', ReferenceType::REPLY_TO)->pluck('ref_entry_id')->toArray();

        if (!empty($referenceIds))
            $this->markAsEntriesAsRead($referenceIds, $level + 1);
    }
}
