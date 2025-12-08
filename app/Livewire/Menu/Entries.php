<?php

namespace App\Livewire\Menu;

use App\Domain\Core\Actions\MarkAsReadAction;
use Livewire\Component;

class Entries extends Component
{
    public array $entryIds;
    public bool $isTop = false;
    public ?string $loadNewestLink = null;
    public ?string $loadMoreLink = null;


    public function mount(array $entryIds = [], bool $isTop = false, ?string $loadNewestLink = '', ?string $loadMoreLink = ''): void
    {
        $this->entryIds         = $entryIds;
        $this->isTop            = $isTop;
        $this->loadNewestLink   = $loadNewestLink;
        $this->loadMoreLink     = $loadMoreLink;
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
