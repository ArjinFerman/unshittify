<?php

namespace App\Domain\Legacy\V1\DTO;

use App\Domain\Core\DTO\EntryReferenceDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Enums\ReferenceType;
use App\Support\CompositeId;
use Illuminate\Support\Collection;
use stdClass;

class LegacyEntryReferenceDTO extends EntryReferenceDTO
{
    /**
     * @param stdClass $v1MainEntry
     * @param Collection<int, stdClass> $v1EntriesReferences
     * @return Collection<int, LegacyEntryDTO>
     */
    public static function collectFromRawDB(stdClass $v1MainEntry, Collection $v1EntriesReferences): Collection
    {
        $result = new Collection();
        $v1EntryReferences = $v1EntriesReferences->get($v1MainEntry->id);

        foreach ($v1EntryReferences ?? [] as $v1EntryReference) {
            $metadata = json_decode($v1EntryReference->metadata);

            switch ($v1EntryReference->ref_type) {
                case 'reply_to':
                    $result->add(new EntryReferenceDTO(
                        entry_composite_id: CompositeId::create(ExternalSourceType::TWITTER, $metadata->tweet_id),
                        ref_entry_composite_id: CompositeId::create(ExternalSourceType::TWITTER, $v1MainEntry->metadata->tweet_id),
                        ref_type: ReferenceType::REPLY_FROM,
                        referenced_entry: null, // We just need the reference itself, the referenced entry will be imported independently
                    ));
                    break;
                default:
                    $result->add(new EntryReferenceDTO(
                        entry_composite_id: CompositeId::create(ExternalSourceType::TWITTER, $v1MainEntry->metadata->tweet_id),
                        ref_entry_composite_id: CompositeId::create(ExternalSourceType::TWITTER, $metadata->tweet_id),
                        ref_type: ReferenceType::from($v1EntryReference->ref_type),
                        referenced_entry: null, // We just need the reference itself, the referenced entry will be imported independently
                    ));
            }
        }

        return $result;
    }
}
