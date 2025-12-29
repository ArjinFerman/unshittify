<?php

namespace App\Domain\Legacy\V1\DTO;

use App\Domain\Core\DTO\EntryReferenceDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Enums\ReferenceType;
use App\Support\CompositeId;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;

class LegacyEntryReferenceDTO extends EntryReferenceDTO
{
    /**
     * @param stdClass $v1MainEntry
     * @param Collection<int, stdClass> $v1EntriesReferences
     * @return Collection<int, LegacyEntryDTO>
     */
    public static function collectFromRawDB(stdClass $v1MainEntry, Collection $v1EntriesReferences, string &$content): Collection
    {
        $result = new Collection();
        $v1EntryReferences = $v1EntriesReferences->get($v1MainEntry->id);

        foreach ($v1EntryReferences ?? [] as $v1EntryReference) {
            $metadata = json_decode($v1EntryReference->metadata);

            switch ($v1EntryReference->ref_type) {
                case 'reply_to':
                    $result->add(new self(
                        entry_composite_id: CompositeId::create(ExternalSourceType::TWITTER, $metadata->tweet_id),
                        ref_entry_composite_id: CompositeId::create(ExternalSourceType::TWITTER, $v1MainEntry->metadata->tweet_id),
                        ref_type: ReferenceType::REPLY_FROM,
                        referenced_entry: null, // We just need the reference itself, the referenced entry will be imported independently
                    ));
                    break;
                default:
                    $result->add(new self(
                        entry_composite_id: CompositeId::create(ExternalSourceType::TWITTER, $v1MainEntry->metadata->tweet_id),
                        ref_entry_composite_id: CompositeId::create(ExternalSourceType::TWITTER, $metadata->tweet_id),
                        ref_type: ReferenceType::from($v1EntryReference->ref_type),
                        referenced_entry: null, // We just need the reference itself, the referenced entry will be imported independently
                    ));
            }
        }

        preg_match('/x-entry\.link url="([^\"]+)"/', $content, $matches);
        foreach ($matches ?? [] as $match) {
            if ($match == $matches[0])
                continue;

            $url = (parse_url($match, PHP_URL_SCHEME) ?: 'http') . '://' . parse_url($match, PHP_URL_HOST) . (parse_url($match, PHP_URL_PATH) ?: '/');

            $link = LegacyEntryDTO::createFromLinkData($url);
            $result->add(new self(
                entry_composite_id: CompositeId::create(ExternalSourceType::TWITTER, $v1MainEntry->metadata->tweet_id),
                ref_entry_composite_id: $link->composite_id,
                ref_type: ReferenceType::LINK,
                referenced_entry: $link
            ));

            $content = Str::replace("url=\"$match\"", "compositeId=\"$link->composite_id\"", $content);
        }

        return $result;
    }
}
