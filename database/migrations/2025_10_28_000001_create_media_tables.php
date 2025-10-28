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
        Schema::create('media', function (Blueprint $table) {
            $table->string( 'composite_id', 256)->primary();
            $table->enum('type', ['image', 'video']); // MediaType::cases()
            $table->string('url');
            $table->string('content_type');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('mediables', function (Blueprint $table) {
            $table->primary(['media_composite_id', 'mediable_composite_id', 'mediable_type']);
            $table->string('media_composite_id');
            $table->string('mediable_composite_id');
            $table->string('mediable_type');
            $table->timestamps();

            $table->foreign('media_composite_id')->references('composite_id')->on('media');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('mediables');
    }
};
