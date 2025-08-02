<?php

namespace App\Livewire\Entry;

use App\Domain\Core\Actions\ChangeFeedStatusAction;
use App\Domain\Core\Actions\ToggleEntryTagStateAction;
use App\Domain\Core\Enums\CoreTagType;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Entry;
use Livewire\Component;

class Menu extends Component
{
    public ?Entry $entry = null;

    public function mount(?Entry $entry): void
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
        return view('livewire.entry.menu');
    }
}
