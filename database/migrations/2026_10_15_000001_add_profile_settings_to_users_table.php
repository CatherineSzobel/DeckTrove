<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Edit profile's Privacy settings.
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('show_liked_decks')->default(false);
            $table->boolean('allow_comments')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['show_liked_decks', 'allow_comments']);
        });
    }
};
