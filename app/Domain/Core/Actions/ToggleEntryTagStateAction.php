<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\Enums\CoreTagType;
use App\Domain\Core\Models\Entry;

class ToggleEntryTagStateAction extends BaseAction
{
    const MAX_LEVEL = 10;

    /**
     * @throws \Throwable
     */
    public function execute(Entry $entry, int $tagId): void
    {
        $this->optionalTransaction(function () use ($entry, $tagId) {
            $this->toggleTag($entry, $tagId);
        });
    }

    protected function toggleTag(Entry $entry, int $tagId, int $level = 0): void
    {
        if ($level > self::MAX_LEVEL)
            return;

        foreach ($entry->references as $reference) {
            $this->toggleTag($reference, $tagId, $level + 1);
        }

        if ($entry->hasTag($tagId))
            $entry->tags()->detach($tagId);
        else
            $entry->tags()->attach($tagId);
    }
}
