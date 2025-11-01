<?php

namespace App\View\Components\Entry;

use App\Domain\Twitter\DTO\TweetEntryDTO as APITweetDTO;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TweetDto extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public APITweetDTO $entry, public int $level = 0)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $displayEntry = $this->entry->retweet ?? $this->entry;
        $displayEntry->media = $displayEntry->media->keyBy('media_object_id');

        return view('components.entry.tweet-dto', [
            'displayEntry' => $displayEntry,
            'isRetweeted' => !is_null($this->entry->retweet),
        ]);
    }
}
