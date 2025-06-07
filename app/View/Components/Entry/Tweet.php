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
        $reference = $this->entry->optimizedReferences()->first();
        $isRetweeted = ($reference?->pivot?->ref_type == ReferenceType::REPOST);
        $mainEntry = ($isRetweeted ? $reference : $this->entry);

        return view('components.entry.tweet', [
            'mainEntry' => $mainEntry,
            'mainContent' => $this->renderComponents($mainEntry),
            'isRetweeted' => $isRetweeted,
        ]);
    }
}
