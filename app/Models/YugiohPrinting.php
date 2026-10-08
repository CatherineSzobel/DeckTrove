<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YugiohPrinting extends Model
{
    public $timestamps = false;

    protected $fillable = ['yugioh_card_id', 'yugioh_set_id', 'code', 'rarity'];

    public function card(): BelongsTo
    {
        return $this->belongsTo(YugiohCard::class, 'yugioh_card_id');
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(YugiohSet::class, 'yugioh_set_id');
    }
}
