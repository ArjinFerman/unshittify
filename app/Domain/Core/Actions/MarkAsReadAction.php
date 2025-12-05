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
    public function execute(array $compositeIds = []): void
    {
        $this->optionalTransaction(function () use ($compositeIds) {
            if (empty($compositeIds)) {
                Entry::query()->update(['is_read' => true]);
            } else {
                $this->markEntriesAsRead($compositeIds);
            }
        });
    }

    protected function markEntriesAsRead(array $compositeIds, int $level = 0): void
    {
        if ($level > self::MAX_LEVEL)
            return;

        Entry::query()
            ->whereIn('composite_id', $compositeIds)
            ->update(['is_read' => true]);

        $referenceIds = EntryReference::whereIn('entry_id', $compositeIds)
            ->whereNot('ref_type', ReferenceType::REPLY_FROM)->pluck('ref_entry_id')->toArray();

        if (!empty($referenceIds))
            $this->markEntriesAsRead($referenceIds, $level + 1);
    }
}
