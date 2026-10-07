<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Set once `php artisan yugioh:images` has copied the card's images to our own storage.
        Schema::table('yugioh_cards', function (Blueprint $table) {
            $table->timestamp('images_hosted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('yugioh_cards', function (Blueprint $table) {
            $table->dropColumn('images_hosted_at');
        });
    }
};
