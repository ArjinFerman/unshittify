<?php

use App\Domain\Core\Enums\MediaType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('core_media', function(Blueprint $table)
        {
            $table->unique(['media_object_id', 'quality'], 'core_media_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('core_media', function(Blueprint $table)
        {
            $table->dropUnique('core_media_unique');
        });
    }
};
