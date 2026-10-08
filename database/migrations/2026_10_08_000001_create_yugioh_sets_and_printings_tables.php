<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Yu-Gi-Oh! products (booster packs, starter decks, tins...). Several products can share a code,
        // so the name (and its slug) identifies a set.
        Schema::create('yugioh_sets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->unsignedInteger('card_count')->nullable();
            $table->date('released_at')->nullable()->index();
            $table->string('image_url')->nullable();
        });

        // Which card was printed in which set, under which print code (e.g. LOB-EN001) and rarity.
        Schema::create('yugioh_printings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('yugioh_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('yugioh_set_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('rarity')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yugioh_printings');
        Schema::dropIfExists('yugioh_sets');
    }
};
