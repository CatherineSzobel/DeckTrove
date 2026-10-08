<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class YugiohSet extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['code', 'name', 'slug', 'card_count', 'released_at', 'image_url'];

    protected function casts(): array
    {
        return ['released_at' => 'date'];
    }

    public function printings(): HasMany
    {
        return $this->hasMany(YugiohPrinting::class);
    }

    /**
     * The table columns for a set from the YGOPRODeck set list.
     */
    public static function attributesFrom(array $set): array
    {
        return [
            'code' => $set['set_code'],
            'name' => $set['set_name'],
            'slug' => Str::slug($set['set_name']),
            'card_count' => $set['num_of_cards'] ?? null,
            'released_at' => $set['tcg_date'] ?? null,
            'image_url' => $set['set_image'] ?? null,
        ];
    }

    /**
     * The set in the field names config/series.php (`yugioh.pack`) reads.
     */
    public function toPackArray(): array
    {
        return [
            'set_code' => $this->code,
            'set_name' => $this->name,
            'slug' => $this->slug,
            'num_of_cards' => $this->card_count,
            'tcg_date' => $this->released_at?->toDateString(),
            'set_image' => $this->image_url,
        ];
    }
}
