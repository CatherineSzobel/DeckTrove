<?php

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
        Schema::create('cards', function (Blueprint $table) {
            $table->id(); // internal ID

        $table->string('game', 20); // yugioh, magic, etc.
        $table->string('external_id')->index(); // YGOPro ID, Scryfall ID, etc.

        $table->string('name');
        $table->string('type')->nullable();
        $table->string('subtype')->nullable();
        
        $table->text('image_url')->nullable();
        
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
