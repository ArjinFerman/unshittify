<?php

namespace App\Livewire\Menu;

use App\Domain\Core\Actions\BulkTagAction;
use App\Domain\Core\Enums\CoreTagType;
use Livewire\Component;

class Entries extends Component
{
    public array $entryIds = [];

    public function mount(array $entryIds): void
    {
        $this->entryIds = $entryIds;
    }

    public function markAsRead(): void
    {
        BulkTagAction::make()->execute($this->entryIds, CoreTagType::READ->value);
        $this->redirectRoute('core.latest');
    }

    public function render()
    {
        return view('livewire.menu.entries');
    }
}
