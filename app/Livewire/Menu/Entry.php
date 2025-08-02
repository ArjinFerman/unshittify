<?php

namespace App\Livewire\Menu;

use App\Domain\Core\Actions\ChangeFeedStatusAction;
use App\Domain\Core\Actions\ToggleEntryTagStateAction;
use App\Domain\Core\Enums\CoreTagType;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Entry as EntryModel;
use Livewire\Component;

class Entry extends Component
{
    public ?EntryModel $entry = null;

    public function mount(?EntryModel $entry): void
    {
        $this->entry = $entry;
    }

    public function toggleRead(): void
    {
        ToggleEntryTagStateAction::make()->execute($this->entry, CoreTagType::READ->value);
        $this->entry->load('tags');
    }

    public function toggleStarred(): void
    {
        ToggleEntryTagStateAction::make()->execute($this->entry, CoreTagType::STARRED->value);
        $this->entry->load('tags');
    }

    public function subscribe(): void
    {
        $displayEntry = $this->entry->displayEntry();
        ChangeFeedStatusAction::make()->execute(
            $displayEntry->feed,
            $displayEntry->feed->status == FeedStatus::ACTIVE ? FeedStatus::INACTIVE : FeedStatus::ACTIVE
        );

        $displayEntry->feed->refresh();
    }

    public function render()
    {
        return view('livewire.menu.entry');
    }
}
