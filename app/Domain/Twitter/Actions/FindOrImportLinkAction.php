<?php

namespace App\Domain\Twitter\Actions;

use App\Domain\Core\Actions\BaseAction;
use App\Domain\Core\Actions\FindOrCreateAuthorAction;
use App\Domain\Core\Actions\FindOrCreateFeedAction;
use App\Domain\Core\DTO\LinkDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Models\Entry;
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
            $page = Page::whereUrl($linkData->expanded_url)->first();
            if ($page)
                return $page;

            if (!$linkData->title)
                return null;

            $linkUri = new Uri($linkData->expanded_url);
            $author = FindOrCreateAuthorAction::make()->withoutTransaction()
                ->execute($linkData->author ?? $linkUri->host(), [
                    'description' => '',
                ]);

            $feed = FindOrCreateFeedAction::make()->withoutTransaction()->execute(
                "{$linkUri->scheme()}://{$linkUri->host()}",
                ExternalSourceType::WEB,
                $author,
                $linkUri->host(),
            );

            $page = new Page();
            $page->type = Page::class;
            $page->url = $linkData->expanded_url;
            $page->title = $linkData->title;
            $page->content = $linkData->description;
            $page->published_at = now();

            $page->feed()->associate($feed);
            $page->save();

            return $page;
        });
    }
}
