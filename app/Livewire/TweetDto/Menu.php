<?php

namespace App\Livewire\TweetDto;

use App\Domain\Core\Actions\ChangeFeedStatusAction;
use App\Domain\Core\Actions\ToggleEntryTagStateAction;
use App\Domain\Core\Enums\CoreTagType;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Entry;
use App\Domain\Twitter\Models\Tweet;
use Livewire\Component;

class Menu extends Component
{
    public ?string $rest_id = null;
    public ?string $url = null;

    public ?Entry $entry = null;

    public function mount(?string $rest_id, ?string $url): void
    {
        $this->rest_id = $rest_id;
        $this->url = $url;
    }

    public function toggleRead(): void
    {
        if (!$this->entry && $this->getEntry()->isRead())
            return;

        ToggleEntryTagStateAction::make()->execute($this->entry, CoreTagType::READ->value);
        $this->entry->load('tags');
    }

    public function toggleStarred(): void
    {
        if (!$this->entry && $this->getEntry()->isRead())
            return;

        ToggleEntryTagStateAction::make()->execute($this->entry, CoreTagType::STARRED->value);
        $this->entry->load('tags');
    }

    public function subscribe(): void
    {
        if (!$this->entry && $this->getEntry()->feed->status == FeedStatus::ACTIVE)
            return;

        ChangeFeedStatusAction::make()->execute(
            $this->entry->feed,
            $this->entry->feed->status == FeedStatus::ACTIVE ? FeedStatus::INACTIVE : FeedStatus::ACTIVE
        );

        $this->entry->feed->refresh();
    }

    public function render()
    {
        return view('livewire.tweet-dto.menu');
    }

    protected function getEntry(): Entry
    {
        if (!$this->entry) {
            $this->entry = Tweet::with(['tags', 'feed'])
                ->where('metadata->tweet_id', $this->rest_id)->first();
        }

        return $this->entry;
    }
}
