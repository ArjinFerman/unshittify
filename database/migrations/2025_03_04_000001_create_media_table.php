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
            $table->unsignedBigInteger('entry_id');
            $table->string('variant_id');
            $table->enum('type', array_column(MediaType::cases(), 'value'));
            $table->string('url');
            $table->string('content_type');
            $table->integer('quality')->default(0);
            $table->json('properties')->nullable();
            $table->timestamps();

            $table->foreign('entry_id')->references('id')->on('core_entries');
            $table->index('variant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_media');
    }
};
