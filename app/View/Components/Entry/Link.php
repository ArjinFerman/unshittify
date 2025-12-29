<?php

namespace App\View\Components\Entry;

use App\Domain\Core\DTO\EntryDTO;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Link extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public ?EntryDTO $entry, public string $compositeId)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $link = $this->entry?->references
            ?->where('ref_entry_composite_id', '=', $this->compositeId)?->first();

        return view('components.entry.link', ['link' => $link?->referenced_entry]);
    }
}
