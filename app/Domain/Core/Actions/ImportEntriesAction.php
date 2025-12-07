<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\DTO\EntryCollectionDTO;
use App\Domain\Core\DTO\EntryDTO;
use App\Domain\Core\DTO\MediableDTO;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\EntryReference;
use App\Domain\Core\Models\Feed;
use App\Domain\Core\Models\Media;
use App\Domain\Core\Models\Mediable;
use Illuminate\Support\Collection;

class ImportEntriesAction extends BaseAction
{
    /**
     * @param EntryCollectionDTO $tweets
     * @throws \Throwable
     */
    public function execute(EntryCollectionDTO $tweets): void
    {
        $this->withoutTransaction()->optionalTransaction(function () use ($tweets) {
            $flatEntries = $this->flattenEntries($tweets->items);
            $importEntries = [];
            $feeds = [];
            $references = [];
            $media = [];
            $mediables = [];

            foreach ($flatEntries as $entry) {
                $importEntries[] = $entry->except('feed', 'references', 'media', 'tags')->toArray();
                $feeds[] = $entry->feed->except('author')->toArray();
                $references = array_merge(
                    $entry->references?->map(fn($reference) => $reference->except('referenced_entry'))?->toArray() ?? [],
                    $references
                );

                foreach ($entry->media ?? [] as $mediaEntry) {
                    $media[] = $mediaEntry->toArray();
                    $mediables[] = (new MediableDTO($mediaEntry->composite_id, $entry->composite_id, Entry::class))->toArray();
                }
            }

            Feed::query()->upsert($feeds, ['composite_id'], ['updated_at']);
            Entry::query()->upsert($importEntries, ['composite_id'], ['title', 'content', 'published_at', 'metadata', 'updated_at']);
            EntryReference::query()->upsert($references, ['entry_composite_id', 'ref_entry_composite_id', 'ref_type']);
            Media::query()->upsert($media, ['composite_id']);
            Mediable::query()->upsert($mediables, ['media_composite_id', 'mediable_composite_id', 'mediable_type']);
        });
    }

    /**
     * @param Collection<int, EntryDTO> $entries
     * @return Collection<int, EntryDTO>
     */
    protected function flattenEntries(Collection $entries): Collection
    {
        $references = $entries->pluck('references.*.referenced_entry')->flatten()->filter();

        if ($references->isNotEmpty())
            $references = $this->flattenEntries($references);

        return $entries->merge($references);
    }
}
