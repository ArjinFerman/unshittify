<?php

namespace App\View\Components\Entry;

use App\Domain\Core\DTO\EntryDTO;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Entry extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public EntryDTO $entry, public int $level = 0)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $displayEntry = $this->entry->repost() ?? $this->entry;
        $displayEntry->media = $displayEntry->media->keyBy('composite_id');

        return view('components.entry.entry', [
            'displayEntry' => $displayEntry,
            'isRetweeted' => $this->entry->isRepost(),
        ]);
    }
}
