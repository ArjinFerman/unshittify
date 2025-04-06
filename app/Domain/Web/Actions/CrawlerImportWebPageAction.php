<?php

namespace App\Domain\Web\Actions;

use App\Domain\Core\Actions\BaseAction;
use App\Domain\Core\Actions\FindOrCreateAuthorAction;
use App\Domain\Core\Models\Entry;
use App\Domain\Web\Models\Page;
use DOMXPath;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;
use League\HTMLToMarkdown\HtmlConverter;

class CrawlerImportWebPageAction extends BaseAction
{
    protected $blacklist = [];

    public function __construct()
    {
        $this->blacklist = config('web.crawler.blacklist') ?? [];
    }

    /**
     * @throws \Throwable
     */
    public function execute(string $url, bool $withContent = false): ?Entry
    {
        if ($this->isInBlacklist($url))
            return null;

        return $this->optionalTransaction(function () use ($url, $withContent) {
            $pageResponse = $this->getPage($url);

            $entry = Entry::whereUrl($url)->first();
            if (!$entry) {
                $entry = new Entry;

                $body = $pageResponse->body();
                $doc = new \DOMDocument();
                $doc->loadHTML($body, LIBXML_NOWARNING | LIBXML_NOERROR);
                $xpath = new DOMXPath($doc);

                $finalUrl = $this->getFinalUrl($pageResponse, $xpath);

                $page = Page::whereCanonicalUrl($finalUrl)->first();
                if(!$page)
                    $page = new Page;

                $page->variant_url = $finalUrl;
                if ($withContent)
                    $page->full_content = $this->cleanUpContent($body);

                $page->save();

                $author = FindOrCreateAuthorAction::make()->withoutTransaction()->execute($finalUrl->host(), [
                    'description' => $xpath->query('//meta[@name="description"]/@content')->item(0)?->nodeValue,
                ]);

                $entry->author_id = $author->id;
                $entry->entryable_type = Page::class;
                $entry->entryable_id = $page->id;
                $entry->url = $url;
                $entry->title = $xpath->query('//title')->item(0)?->nodeValue;
                $entry->content = $this->getContentSummary($xpath);

                $entry->save();
            }

            return $entry;
        });
    }

    protected function cleanUpContent(string $content): string
    {
        $converter = new HtmlConverter([
            'remove_nodes' => 'head script nav header',
            'strip_tags' => true,
        ]);
        $content = $converter->convert($content);

        for ($i = 0; $i < 4; $i++)
            $content = preg_replace('~[ \t\r]+~u', " ", trim($content));

        return $content;
    }

    protected function getContentSummary(DOMXPath $xpath): ?string
    {
        return $xpath->query('//meta[@name="twitter:description"]')->item(0)?->attributes?->getNamedItem("content")?->nodeValue
            ?? $xpath->query('//meta[@name="description"]')->item(0)?->attributes?->getNamedItem("content")?->nodeValue;
    }

    protected function getFinalUrl(Response $pageResponse, DOMXPath $xpath): Uri
    {
        return new Uri(
            $xpath->query('//link[@rel="canonical"]')->item(0)?->attributes?->getNamedItem("href")?->nodeValue
            ?? getCleanUrl($pageResponse->handlerStats()['url'])
        );
    }

    protected function isInBlacklist(string $url): bool
    {
        $tld = explode('.', (new Uri($url))->host());
        $tld = implode('.', array_slice($tld, -2, 2));

        return $this->blacklist[$tld] ?? false;
    }

    /**
     * @throws \Exception
     */
    protected function getPage(string $url): Response
    {
        $finalUrl = new Uri(getCleanUrl($url));

        $response = Http::withHeaders(config('web.crawler.request_headers'))
            ->withOptions([
                'track_redirects' => true,
            ])
            ->get($finalUrl);

        if ($response->failed()) {
            throw new \Exception("Couldn't retrieve page from URL '{$finalUrl->getUri()}'");
        }

        return $response;
    }
}
