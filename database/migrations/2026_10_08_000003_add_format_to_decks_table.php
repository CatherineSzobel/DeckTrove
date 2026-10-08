<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The format the deck is built for: a key of the series' `deck.formats`. Null means Casual.
        Schema::table('decks', function (Blueprint $table) {
            $table->string('format', 32)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('decks', function (Blueprint $table) {
            $table->dropColumn('format');
        });
    }
};
