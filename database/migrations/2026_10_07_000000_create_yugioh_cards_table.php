<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Yu-Gi-Oh! cards imported from the YGOPRODeck dump (php artisan yugioh:import).
        // Filterable fields get their own indexed columns; the full card stays in `data`.
        Schema::create('yugioh_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name')->index();
            $table->string('type')->index();
            $table->string('race')->nullable()->index();
            $table->string('attribute')->nullable()->index();
            $table->string('archetype')->nullable()->index();
            $table->text('desc')->nullable();
            $table->json('data');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yugioh_cards');
    }
};
