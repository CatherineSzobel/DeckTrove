<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Older code could create the same card twice. Point decks at the oldest row and drop the rest,
        // otherwise the unique index below can't be created.
        $duplicates = DB::table('cards')
            ->select('game', 'external_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('game', 'external_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $extraIds = DB::table('cards')
                ->where('game', $duplicate->game)
                ->where('external_id', $duplicate->external_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->pluck('id');

            DB::table('deck_cards')->whereIn('card_id', $extraIds)->update(['card_id' => $duplicate->keep_id]);
            DB::table('cards')->whereIn('id', $extraIds)->delete();
        }

        Schema::table('cards', function (Blueprint $table) {
            // Lookups always include the game, so the unique index replaces the external_id index.
            $table->dropIndex(['external_id']);
            $table->unique(['game', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropUnique(['game', 'external_id']);
            $table->index('external_id');
        });
    }
};
