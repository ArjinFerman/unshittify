<?php

use App\Domain\Core\Enums\CoreTagType;
use App\Domain\Core\Models\Tag;
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
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('authorables', function (Blueprint $table) {
            $table->primary(['authorable_composite_id', 'authorable_type', 'author_id']);
            $table->string('authorable_composite_id');
            $table->string('authorable_type');
            $table->unsignedBigInteger('author_id');

            $table->foreign('author_id')->references('id')->on('authors');
            $table->index(['authorable_composite_id', 'authorable_type']);
        });

        Schema::create('feeds', function (Blueprint $table) {
            $table->string( 'composite_id', 256)->primary();

            $table->string('handle');
            $table->string('name')->nullable();
            $table->enum('status', ['preview', 'active', 'inactive']); // FeedStatus::cases()
            $table->string('url');
            $table->json('metadata')->nullable();

            $table->timestamps();
        });

        Schema::create('entries', function (Blueprint $table) {
            $table->string( 'composite_id', 256)->primary();
            $table->string( 'feed_composite_id', 256);
            $table->string('url');
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->foreign('feed_composite_id')->references('composite_id')->on('feeds');
            $table->index('url');
            $table->index('title');
            $table->index('created_at');
            $table->index('published_at');
        });

        Schema::create('entry_references', function (Blueprint $table) {
            $table->string( 'entry_composite_id', 256);
            $table->string( 'ref_entry_composite_id', 256);
            $table->enum('ref_type', ['link', 'quote', 'repost', 'reply_from']); // ReferenceType::cases()

            $table->primary(['entry_composite_id', 'ref_entry_composite_id', 'ref_type']);
            //$table->foreign('ref_entry_composite_id')->references('composite_id')->on('entries');
            $table->index('ref_type');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->primary(['taggable_composite_id', 'taggable_type', 'tag_id']);
            $table->string('taggable_composite_id');
            $table->string('taggable_type');
            $table->unsignedBigInteger('tag_id');

            $table->foreign('tag_id')->references('id')->on('tags');
        });

        Schema::create('feed_errors', function (Blueprint $table) {
            $table->id();
            $table->string('feed_composite_id');

            $table->string('message');

            $table->timestamps();

            $table->foreign('feed_composite_id')->references('composite_id')->on('feeds');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
        Schema::dropIfExists('feeds');
        Schema::dropIfExists('entries');
        Schema::dropIfExists('entryable');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('taggables');
    }
};
