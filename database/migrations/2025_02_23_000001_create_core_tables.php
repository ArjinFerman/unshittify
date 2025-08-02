<?php

use App\Domain\Core\Enums\CoreTagType;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Enums\FeedType;
use App\Domain\Core\Enums\ReferenceType;
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
        Schema::create('core_authors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->foreign('parent_id')->references('id')->on('core_authors');
        });

        Schema::create('core_feeds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id');

            $table->string('name');
            $table->enum('type', array_column(FeedType::cases(), 'value'));
            $table->enum('status', array_column(FeedStatus::cases(), 'value'));
            $table->string('url');

            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('core_authors');
            $table->unique(['type', 'url']);
        });

        Schema::create('core_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feed_id');
            $table->string('url');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->string('type');
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->foreign('feed_id')->references('id')->on('core_feeds');
            $table->index('type');
            $table->index('url');
            $table->index('title');
            $table->index('created_at');
            $table->index('published_at');
        });

        Schema::create('core_entry_references', function (Blueprint $table) {
            $table->primary(['ref_path']);
            $table->unsignedBigInteger('entry_id');
            $table->unsignedBigInteger('ref_entry_id');
            $table->enum('ref_type', array_column(ReferenceType::cases(), 'value'));
            $table->string('ref_path');

            $table->foreign('entry_id')->references('id')->on('core_entries');
            $table->foreign('ref_entry_id')->references('id')->on('core_entries');
        });

        Schema::create('core_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('core_taggables', function (Blueprint $table) {
            $table->primary(['tag_id', 'taggable_id', 'taggable_type']);
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('taggable_id');
            $table->string('taggable_type');
            $table->timestamps();

            $table->foreign('tag_id')->references('id')->on('core_tags');
        });

        Tag::create(['name' => CoreTagType::READ->name]);
        Tag::create(['name' => CoreTagType::STARRED->name]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_authors');
        Schema::dropIfExists('core_feeds');
        Schema::dropIfExists('core_entries');
        Schema::dropIfExists('core_entryable');
        Schema::dropIfExists('core_tags');
        Schema::dropIfExists('core_taggables');
    }
};
