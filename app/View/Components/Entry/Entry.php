<?php

namespace App\View\Components\Entry;

use App\Domain\Core\DTO\EntryDTO;
use Closure;
use Illuminate\Contracts\View\View;

class Entry extends BaseEntry
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
        $displayContent = $this->renderComponents($displayEntry);

        return view('components.entry.entry', [
            'displayEntry' => $displayEntry,
            'displayContent' => $displayContent,
            'isRetweeted' => $this->entry->isRepost(),
        ]);
    }
}
