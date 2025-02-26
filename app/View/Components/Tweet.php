<?php

namespace App\View\Components;

use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry as EntryModel;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tweet extends Component
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

        return view('components.entry.tweet', [
            'mainEntry' => ($isRetweeted ? $reference : $this->entry),
            'isRetweeted' => $isRetweeted,
        ]);
    }
}
