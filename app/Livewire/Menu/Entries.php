<?php

namespace App\Livewire\Menu;

use App\Domain\Core\Actions\BulkTagAction;
use App\Domain\Core\Actions\TagAllAction;
use App\Domain\Core\Enums\CoreTagType;
use Livewire\Component;

class Entries extends Component
{
    public array $entryIds = [];
    public bool $showMarkPage = true;
    public bool $showMarkAll = false;


    public function mount(array $entryIds, bool $showMarkPage = true, bool $showMarkAll = false): void
    {
        $this->entryIds         = $entryIds;
        $this->showMarkPage     = $showMarkPage;
        $this->showMarkAll      = $showMarkAll;
    }

    public function markPageAsRead(): void
    {
        BulkTagAction::make()->execute($this->entryIds, CoreTagType::READ->value);
        $this->redirectRoute('core.latest');
    }

    public function markAllAsRead(): void
    {
        TagAllAction::make()->execute(CoreTagType::READ->value);
        $this->redirectRoute('core.latest');
    }

    public function render()
    {
        return view('livewire.menu.entries');
    }
}
