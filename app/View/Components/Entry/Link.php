<?php

namespace App\View\Components\Entry;

use App\Domain\Core\Models\Entry;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Link extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public string $url)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $link = Entry::whereUrl($this->url)->first();
        return view('components.entry.link', ['link' => $link]);
    }
}
