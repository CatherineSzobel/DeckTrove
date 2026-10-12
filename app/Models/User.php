<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['username', 'email', 'password', 'about', 'avatar'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function decks(): HasMany
    {
        return $this->hasMany(Deck::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(DeckLike::class);
    }

    /**
     * The first letter of the username, uppercased (accents included), shown when there's no avatar.
     */
    protected function initial(): Attribute
    {
        return Attribute::get(fn () => Str::upper(Str::substr((string) $this->username, 0, 1)));
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar ? Storage::disk(config('filesystems.media'))->url($this->avatar) : null;
    }
}
