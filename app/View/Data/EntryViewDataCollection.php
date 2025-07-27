<?php

namespace App\View\Data;

use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\Media;
use App\Domain\Core\Models\Tag;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @implements Collection<mixed, Entry>
 */
class EntryViewDataCollection extends Collection
{
    protected array $entryPaths;

    public function __construct(Arrayable|iterable|null  $items = [])
    {
        $this->addEntries($items);
    }

    public function addEntries(Arrayable|iterable|null $entries): void
    {
        foreach ($entries as $entry) {
            $this->addEntry($entry);
        }
    }

    public function addEntry(Entry $entry): void
    {
        $this->checkRelation($entry, 'references');
        $this->checkRelation($entry, 'media');
        $this->checkRelation($entry, 'tags');

        /** @var Entry $parent */
        $parent = null;

        if ($entry->ref_path) {
            $pathSegments = array_filter(explode('/', $entry->ref_path));
            $this->entryPaths[$entry->id] = $pathSegments;

            foreach ($pathSegments as $id) {
                if ($id == $entry->id)
                    break;

                $parent = $parent?->references?->get($id) ?? $this->get($id);
            }
        }

        if ($parent) {
            $this->checkRelation($parent, 'references');
            $parent->references->put($entry->id, $entry);
            $this->forget($entry->id);
        } else if (!isset($this->entryPaths[$entry->id]) && !$this->has($entry->id)) {
            $this->entryPaths[$entry->id] = [$entry->id];
            $this->put($entry->id, $entry);
        }
    }

    public function addMedia(Media $media): void
    {
        $this->attachToChildEntry($media, 'media', 'mediable_id');
    }

    public function addTag(Tag $tag): void
    {
        $this->attachToChildEntry($tag, 'tags', 'taggable_id');
    }

    protected function attachToChildEntry(mixed $item, string $itemProperty, string $referenceProperty): void
    {
        /** @var Entry $entry */
        $entry = null;
        foreach ($this->entryPaths[$item->$referenceProperty] as $entryId)
            $entry = $entry?->references?->get($entryId) ?? $this->items[$entryId];

        $entry->$itemProperty->add($item);
    }

    protected function checkRelation(Entry $entry, string $relation): void
    {
        if (!$entry->relationLoaded($relation))
            $entry->setRelation($relation, new Collection());
    }
}
