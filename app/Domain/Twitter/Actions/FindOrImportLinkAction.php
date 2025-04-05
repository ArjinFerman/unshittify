<?php

namespace App\Domain\Twitter\Actions;

use App\Domain\Core\Actions\BaseAction;
use App\Domain\Core\Actions\FindOrCreateAuthorAction;
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

            $entry = new Entry;
            $entry->url = $linkData->expanded_url;
            $entry->title = $linkData->title;
            $entry->content = $linkData->description;
            $entry->published_at = now();

            $entry->entryable()->associate($page);
            $entry->author()->associate($author);
            $entry->save();

            return $entry;
        });
    }
}
