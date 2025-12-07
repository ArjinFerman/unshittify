<?php

namespace App\View\Components;

use App\Domain\Core\DTO\EntryDTO;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Link extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public ?EntryDTO $entry, public string $url)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $link = $this->entry?->references
            ?->where('url', '=', $this->url)?->first();

        return view('components.link', ['link' => $link, 'url' => $this->url]);
    }
}
