<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\DTO\EntryDTO;
use App\Domain\Core\DTO\TagDTO;
use App\Domain\Core\Models\Entry;
use Illuminate\Support\Collection;

class ToggleEntryTagStateAction extends BaseAction
{
    /**
     * @throws \Throwable
     */
    public function execute(EntryDTO $entryData, int $tagId): Collection
    {
        return $this->optionalTransaction(function () use ($entryData, $tagId) {
            $entry = Entry::with('tags')->findOrFail($entryData->composite_id);

            if ($entry->hasTag($tagId))
                $entry->tags()->detach($tagId);
            else
                $entry->tags()->attach($tagId);

            $entry->load('tags');
            return TagDTO::collect($entry->tags);
        });
    }
}
