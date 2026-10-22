<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('list', 10)->default('owned');
            $table->string('game', 20);
            $table->string('card_key', 64);
            $table->foreignId('card_id')->constrained('cards');
            // '' = unspecified: unique indexes treat NULLs as distinct on every database we target.
            $table->string('printing', 64)->default('');
            $table->unsignedSmallInteger('quantity');
            $table->timestamps();

            $table->unique(['user_id', 'list', 'game', 'card_key', 'printing']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_items');
    }
};
