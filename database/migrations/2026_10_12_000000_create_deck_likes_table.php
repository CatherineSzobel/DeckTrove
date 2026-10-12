<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per user per liked deck. Like counts and trending are counted from here; nothing is stored on decks.
        Schema::create('deck_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deck_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['user_id', 'deck_id']);
            $table->index(['deck_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deck_likes');
    }
};
