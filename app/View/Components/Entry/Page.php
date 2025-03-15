<?php

namespace App\View\Components\Entry;

use App\Domain\Core\Models\Entry;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Page extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public ?Entry $entry)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.entry.link', ['link' => $this->entry, 'url' => $this->entry->url]);
    }
}
