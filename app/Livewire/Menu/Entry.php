<?php

namespace App\Livewire\Menu;

use App\Domain\Core\Actions\ChangeFeedStatusAction;
use App\Domain\Core\Actions\MarkAsReadAction;
use App\Domain\Core\Actions\ToggleEntryTagStateAction;
use App\Domain\Core\DTO\EntryDTO;
use App\Domain\Core\DTO\EntryReferenceDTO;
use App\Domain\Core\Enums\CoreTagType;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Feed;
use Livewire\Component;

class Entry extends Component
{
    public ?EntryDTO $entry = null;

    public function mount(?EntryDTO $entry): void
    {
        $this->entry = $entry;
    }

    public function toggleRead(): void
    {
        $this->entry->is_read = !$this->entry->is_read;
        $this->entry->references->each(fn (EntryReferenceDTO $ref) => $ref->referenced_entry->is_read = $this->entry->is_read);

        MarkAsReadAction::make()->execute([$this->entry->composite_id]);
    }

    public function toggleStarred(): void
    {
        $tags = ToggleEntryTagStateAction::make()->execute($this->entry, CoreTagType::STARRED->value, false);
        $this->entry->tags = $tags;
    }

    public function subscribe(): void
    {
        $displayEntry = $this->entry->displayEntry();
        $displayEntry->feed->status = ChangeFeedStatusAction::make()->execute(
            $displayEntry->feed,
            $displayEntry->feed->status == FeedStatus::ACTIVE ? FeedStatus::INACTIVE : FeedStatus::ACTIVE
        );
    }

    public function render()
    {
        return view('livewire.menu.entry');
    }
}
