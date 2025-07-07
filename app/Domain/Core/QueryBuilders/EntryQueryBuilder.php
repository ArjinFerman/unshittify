<?php

namespace App\Domain\Core\QueryBuilders;

use App\Domain\Core\Models\Author;
use App\Domain\Core\Models\Entry;
use App\Support\Query\EagerLoadJoinBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EntryQueryBuilder extends EagerLoadJoinBuilder
{
    public function withViewData(): static
    {
        return $this->withJoin('feed')
            ->withJoin('feed.author')
            ->join('core_mediables', function ($join) {
                $join->on('core_mediables.mediable_id', '=', 'core_authors.id');
                $join->on('core_mediables.mediable_type', '=', Author::class);
            })
            ->join('core_media', function ($join) {
                $join->on('core_media.id', '=', 'core_mediables.media_id');
            })
            ->addSelect([
                'core_media.id AS feed.author.avatar.id',
                'core_media.media_object_id AS feed.author.avatar.media_object_id',
                'core_media.type AS feed.author.avatar.type',
                'core_media.url AS feed.author.avatar.url',
                'core_media.content_type AS feed.author.avatar.content_type',
                'core_media.quality AS feed.author.avatar.quality',
                'core_media.properties AS feed.author.avatar.properties',
            ]);
    }

    public function withReferenceData(): static
    {
        return $this
            ->join('core_entry_references', 'core_entry_references.ref_entry_id', '=', 'core_entries.id', 'left')
            ->addSelect([
                'core_entry_references.ref_path',
                'core_entry_references.ref_type',
            ]);
    }

    /***
     * @param Collection<Entry> $entries
     * @return $this
     */
    public function whereReferencesOf(Collection $entries): static
    {
        return $this
            ->whereExists(function (Builder $query) use ($entries) {
                $query->from('core_entries', 'ce_ref')
                    ->where('core_entry_references.ref_path', 'LIKE', DB::raw("CONCAT('%/', ce_ref.id, '/%')"))
                    ->whereIn('ce_ref.id', $entries->pluck('id'));
            });
    }
}
