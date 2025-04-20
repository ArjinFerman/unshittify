<?php

namespace App\Domain\Twitter\Actions;

use App\Domain\Core\Actions\BaseAction;
use App\Domain\Core\Actions\FindOrCreateAuthorAction;
use App\Domain\Core\Actions\FindOrCreateFeedAction;
use App\Domain\Core\Enums\FeedType;
use App\Domain\Core\Models\Entry;
use App\Domain\Twitter\DTO\LinkDTO;
use App\Domain\Web\Models\Page;
use Illuminate\Support\Uri;

class FindOrImportLinkAction extends BaseAction
{
    /**
     * @throws \Throwable
     */
    public function execute(LinkDTO $linkData): ?Entry
    {
        return $this->optionalTransaction(function () use ($linkData) {
            $page = Page::firstOrCreate([
                'variant_url' => $linkData->expanded_url
            ]);

            $entry = $page->entry;
            if ($entry)
                return $entry;

            if (!$linkData->title)
                return null;

            $linkUri = new Uri($linkData->expanded_url);
            $author = FindOrCreateAuthorAction::make()->withoutTransaction()
                ->execute($linkData->author ?? $linkUri->host(), [
                    'description' => '',
                ]);

            $feed = FindOrCreateFeedAction::make()->withoutTransaction()->execute(
                "{$linkUri->scheme()}://{$linkUri->host()}",
                FeedType::WEB,
                $author,
                $linkUri->host(),
            );

            $entry = new Entry;
            $entry->url = $linkData->expanded_url;
            $entry->title = $linkData->title;
            $entry->content = $linkData->description;
            $entry->published_at = now();

            $entry->entryable()->associate($page);
            $entry->feed()->associate($feed);
            $entry->save();

            return $entry;
        });
    }
}
