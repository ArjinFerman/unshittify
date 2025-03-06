<?php

namespace App\View\Components\Entry;

use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry as EntryModel;
use Closure;
use Illuminate\Contracts\View\View;

class Tweet extends BaseEntry
{
    /**
     * Create a new component instance.
     */
    public function __construct(public EntryModel $entry)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $reference = $this->entry->references()->first();
        $isRetweeted = ($reference?->pivot?->ref_type == ReferenceType::REPOST);
        $mainEntry = ($isRetweeted ? $reference : $this->entry);

        return view('components.entry.tweet', [
            'mainEntry' => ($isRetweeted ? $reference : $this->entry),
            'mainContent' => $this->renderMedia($mainEntry),
            'isRetweeted' => $isRetweeted,
        ]);
    }
}
