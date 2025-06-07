<?php

namespace App\View\Components\Entry;

use App\Domain\Core\Models\Entry;
use App\Domain\Web\Models\Page;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Link extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public ?Entry $entry, public string $url)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $link = $this->entry->optimizedReferences()
            ->where('url', '=', $this->url)->first();

        return view('components.entry.link', ['link' => $link]);
    }
}
