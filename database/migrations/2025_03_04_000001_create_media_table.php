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
        Schema::create('core_media', function (Blueprint $table) {
            $table->id();
            $table->string('media_object_id');
            $table->enum('type', array_column(MediaType::cases(), 'value'));
            $table->string('url');
            $table->string('content_type');
            $table->integer('quality')->default(0);
            $table->json('properties')->nullable();
            $table->timestamps();

            $table->index('media_object_id');
        });

        Schema::create('core_mediables', function (Blueprint $table) {
            $table->primary(['media_id', 'mediable_id', 'mediable_type']);
            $table->unsignedBigInteger('media_id');
            $table->unsignedBigInteger('mediable_id');
            $table->string('mediable_type');
            $table->string('purpose');
            $table->timestamps();

            $table->foreign('media_id')->references('id')->on('core_media');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_media');
        Schema::dropIfExists('core_mediables');
    }
};
