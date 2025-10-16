<?php

namespace App\Livewire\Menu;

use App\Domain\Core\Actions\MarkAsReadAction;
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
        MarkAsReadAction::make()->execute($this->entryIds);
        $this->redirectRoute('core.latest');
    }

    public function markAllAsRead(): void
    {
        MarkAsReadAction::make()->execute();
        $this->redirectRoute('core.latest');
    }

    public function render()
    {
        return view('livewire.menu.entries');
    }
}
