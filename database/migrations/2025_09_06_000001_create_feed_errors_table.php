<?php

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
        Schema::create('core_feed_errors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feed_id');

            $table->string('message');

            $table->timestamps();

            $table->foreign('feed_id')->references('id')->on('core_feeds');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_feed_errors');
    }
};
