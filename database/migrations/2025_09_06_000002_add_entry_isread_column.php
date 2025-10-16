<?php

use App\Domain\Core\Enums\CoreTagType;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\Taggable;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('core_entries', function(Blueprint $table)
        {
            $table->boolean('is_read')->default(false);
            $table->index('is_read');
        });

        Entry::query()
            ->whereIn('id', function($query) {
                $query->from('core_taggables')
                    ->whereTaggableType(Entry::class)
                    ->whereTagId(CoreTagType::READ)
                    ->select('taggable_id');
            })
            ->update(['is_read' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Taggable::query()->insertOrIgnoreUsing([
            'tag_id', 'taggable_id', 'taggable_type', 'created_at', 'updated_at'
        ], function ($query) {
            $query->from('core_entries')
                ->where('is_read', true)
                ->select([
                    DB::raw(CoreTagType::READ->value . ' as tag_id'),
                    DB::raw('id as taggable_id'),
                    DB::raw("'" . Entry::class . "' as taggable_type"),
                    DB::raw("'" . Carbon::now() . "' as created_at"),
                    DB::raw("'" . Carbon::now() . "' as updated_at"),
                ]);
        });

        Schema::table('core_entries', function(Blueprint $table)
        {
            $table->dropIndex('core_entries_is_read_index');
            $table->dropColumn('is_read');
        });
    }
};
