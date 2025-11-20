<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Yugioh_Card extends Model
{
    use HasFactory;

    // Table name (optional if it matches 'cards')
    protected $table = 'cards';

    // Primary key is the 'id' field from JSON
    protected $primaryKey = 'id';
    public $incrementing = false; // Because JSON id is provided
    protected $keyType = 'bigint';

    protected $fillable = [
        'id',
        'name',
        'typeline',
        'type',
        'human_readable_card_type',
        'frame_type',
        'desc',
        'race',
        'atk',
        'def',
        'level',
        'attribute',
        'ygoprodeck_url',
        'card_sets',
        'banlist_info',
        'card_images',
        'card_prices',
    ];

    // Cast JSON columns
    protected $casts = [
        'typeline' => 'array',
        'card_sets' => 'array',
        'banlist_info' => 'array',
        'card_images' => 'array',
        'card_prices' => 'array',
    ];
}
