<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\Enums\CoreTagType;
use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\EntryReference;
use App\Domain\Core\Models\Tag;

class BulkTagAction extends BaseAction
{
    const MAX_LEVEL = 10;

    /**
     * @throws \Throwable
     */
    public function execute(array $entryIds, int $tagId): void
    {
        $this->optionalTransaction(function () use ($entryIds, $tagId) {
            $this->addTagToEntries($entryIds, Tag::find($tagId));
        });
    }

    protected function addTagToEntries(array $entryIds, Tag $tag, int $level = 0): void
    {
        if ($level > self::MAX_LEVEL)
            return;

        $tag->entries()->syncWithoutDetaching($entryIds);
        $referenceIds = EntryReference::whereIn('entry_id', $entryIds)
            ->whereNot('ref_type', ReferenceType::REPLY_TO)->pluck('ref_entry_id')->toArray();

        if (!empty($referenceIds))
            $this->addTagToEntries($referenceIds, $tag, $level + 1);
    }
}
