<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\EntryReference;
use App\Domain\Core\Models\Tag;
use App\Domain\Core\Models\Taggable;
use Carbon\Carbon;

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

        $values = [];
        foreach ($entryIds as $entryId) {
            $values[] = [
                'tag_id'=> $tag->id,
                'taggable_id' => $entryId,
                'taggable_type' => Entry::class,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        Taggable::query()->upsert(
            $values,
            ['tag_id', 'taggable_id', 'taggable_type'],
            ['updated_at']
        );

        $referenceIds = EntryReference::whereIn('entry_id', $entryIds)
            ->whereNot('ref_type', ReferenceType::REPLY_FROM)->pluck('ref_entry_id')->toArray();

        if (!empty($referenceIds))
            $this->addTagToEntries($referenceIds, $tag, $level + 1);
    }
}
