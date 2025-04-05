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
    public function __construct(public string $url)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $link = Page::whereVariantUrl($this->url)->first();
        if ($link && $link->parent_id)
            $link = $link->parent;

        $link = $link?->entry;
        return view('components.entry.link', ['link' => $link]);
    }
}
