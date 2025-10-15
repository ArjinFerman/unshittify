<?php

namespace App\Domain\Core\QueryBuilders;

use App\Domain\Core\Models\Author;
use App\Domain\Core\Models\Entry;
use App\Support\Query\EagerLoadJoinBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EntryQueryBuilder extends EagerLoadJoinBuilder
{
    const RECUSRIVE_REF_TABLE = 'reference_tree';

    public function withRecursiveReferences(Collection $entryIds)
    {
        $recursive = DB::table('core_entry_references')
            ->select([
                'entry_id',
                'ref_entry_id',
                'ref_type',
                DB::raw("CONCAT('/', CAST(entry_id AS CHAR(256)), '/', CAST(ref_entry_id AS CHAR(256)), '/') as ref_path"),
                DB::raw('1 as depth')
            ])
            ->whereIn('entry_id', $entryIds)
            ->unionAll(
                DB::table('core_entry_references')
                    ->select([
                        self::RECUSRIVE_REF_TABLE . '.entry_id',
                        self::RECUSRIVE_REF_TABLE . '.ref_entry_id',
                        self::RECUSRIVE_REF_TABLE . '.ref_type',
                        DB::raw("CONCAT(" . self::RECUSRIVE_REF_TABLE . '.ref_path' . ", CAST(core_entry_references.ref_entry_id AS CHAR(256)), '/') as ref_path"),
                        DB::raw(self::RECUSRIVE_REF_TABLE . '.depth + 1 as depth'),
                    ])->join(self::RECUSRIVE_REF_TABLE, self::RECUSRIVE_REF_TABLE. '.entry_id', '=', 'core_entry_references.ref_entry_id')
                    ->where('depth', '<', 3)
            );

        $finalQuery = new self(DB::table(self::RECUSRIVE_REF_TABLE)->withRecursiveExpression(self::RECUSRIVE_REF_TABLE, $recursive));
        $finalQuery->setModel($this->getModel());

        $finalQuery->join(self::RECUSRIVE_REF_TABLE, self::RECUSRIVE_REF_TABLE . '.ref_entry_id', '=', 'core_entries.id')
            ->select([
                self::RECUSRIVE_REF_TABLE . '.ref_path',
                self::RECUSRIVE_REF_TABLE . '.ref_type',
            ])
            ->addSelect('core_entries.*');

        return $finalQuery;
    }

    public function withViewData(): static
    {
        return $this->withJoin('feed')
            ->withJoin('feed.author')
            ->join('core_mediables', function ($join) {
                $join->on('core_mediables.mediable_id', '=', 'core_authors.id');
                $join->on('core_mediables.mediable_type', '=', DB::raw(DB::escape(Author::class)));
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
