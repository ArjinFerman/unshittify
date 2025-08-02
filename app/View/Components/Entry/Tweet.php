<?php

namespace App\View\Components\Entry;

use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Twitter\Models\Tweet as EntryModel;
use Closure;
use Illuminate\Contracts\View\View;

class Tweet extends BaseEntry
{
    /**
     * Create a new component instance.
     */
    public function __construct(public EntryModel $entry, public int $level = 0)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $displayEntry = $this->entry->displayEntry();

        return view('components.entry.tweet', [
            'displayEntry' => $displayEntry,
            'displayContent' => $this->renderComponents($displayEntry),
            'isRetweeted' => ($displayEntry->id != $this->entry->id),
        ]);
    }
}
