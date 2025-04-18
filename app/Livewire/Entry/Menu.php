<?php

namespace App\Livewire\Entry;

use App\Domain\Core\Actions\ToggleEntryTagStateAction;
use App\Domain\Core\Enums\CoreTagType;
use App\Domain\Core\Models\Entry;
use Livewire\Component;

class Menu extends Component
{
    public ?Entry $entry;

    public function mount(?Entry $entry): void
    {
        $this->entry = $entry;
    }

    public function toggleRead(): void
    {
        ToggleEntryTagStateAction::make()->execute($this->entry, CoreTagType::READ->value);
    }

    public function toggleStarred(): void
    {
        ToggleEntryTagStateAction::make()->execute($this->entry, CoreTagType::STARRED->value);
    }

    public function subscribe(): void
    {

    }

    public function render()
    {
        return view('livewire.entry.menu');
    }
}
