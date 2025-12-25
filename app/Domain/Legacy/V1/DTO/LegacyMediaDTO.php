<?php

namespace App\Domain\Legacy\V1\DTO;

use App\Domain\Core\DTO\MediaDTO;
use App\Domain\Core\Enums\MediaType;
use App\Domain\Twitter\DTO\TwitterMediaDTO;
use App\Support\CompositeId;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use stdClass;

class LegacyMediaDTO extends MediaDTO
{
    /**
     * @param stdClass $v1Entry
     * @param Collection<int, stdClass> $v1EntryMedia
     * @return Collection<int, LegacyMediaDTO>
     */
    public static function collectFromRawDB(stdClass $v1Entry, Collection $v1EntryMedia, string &$content): Collection
    {
        $mediaCollection = new Collection();
        foreach ($v1EntryMedia->get($v1Entry->id) ?? [] as $v1Media) {
            $mediaData = new TwitterMediaDTO(
                composite_id: CompositeId::fromString(Str::replace('-', '|', $v1Media->media_object_id)),
                type: MediaType::from($v1Media->type),
                url: $v1Media->url,
                content_type: $v1Media->content_type,
                metadata: json_decode($v1Media->properties, true),
            );

            $mediaCollection->add($mediaData);

            $content = Str::replace(
                "x-media mediaObjectId=\"{$v1Media->media_object_id}\"",
                "x-media compositeId=\"{$mediaData->composite_id}\"",
                $content
            );
        }

        return $mediaCollection;
    }
}
