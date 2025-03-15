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
        Schema::create('web_pages', function (Blueprint $table) {
            $table->id();
            $table->string('canonical_url')->nullable();
            $table->longText('full_content')->nullable();
            $table->timestamps();

            $table->index('canonical_url');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_page');
    }
};
