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
    public function execute(Entry $entry, int $tagId, bool $recursive): void
    {
        $this->optionalTransaction(function () use ($entry, $tagId, $recursive) {
            $this->toggleTag($entry, $tagId, $recursive);
        });
    }

    protected function toggleTag(Entry $entry, int $tagId, bool $recursive = false, int $level = 0): void
    {
        if ($level > self::MAX_LEVEL)
            return;

        if ($recursive) {
            foreach ($entry->references as $reference) {
                $this->toggleTag($reference, $tagId, $level + 1);
            }
        }

        if ($entry->hasTag($tagId))
            $entry->tags()->detach($tagId);
        else
            $entry->tags()->attach($tagId);
    }
}
