<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Comments\Commentable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable implements Commentable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['username', 'email', 'password', 'about', 'avatar', 'show_liked_decks', 'allow_comments'];

    /**
     * The database defaults, also on a model that was just created (before a reload).
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'show_liked_decks' => false,
        'allow_comments' => true,
    ];

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
            'show_liked_decks' => 'boolean',
            'allow_comments' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Comments on this profile and on this user's decks have no foreign key (their target is polymorphic), so
        // they go with the user here, before the database removes the decks. The comments this user wrote go
        // through the user_id foreign key.
        static::deleting(function (User $user) {
            $user->profileComments()->delete();
            Comment::deleteOnDecks($user->decks()->pluck('id'));
        });
    }

    public function decks(): HasMany
    {
        return $this->hasMany(Deck::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(DeckLike::class);
    }

    /** Comments other players (and this user) left on this user's profile; each knows its profile without a query. */
    public function profileComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->chaperone('commentable');
    }

    /** Comments this user wrote, anywhere. */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function commentOwnerId(): int
    {
        return $this->id;
    }

    public function acceptsComments(): bool
    {
        return (bool) $this->allow_comments;
    }

    public function commentsFallbackUrl(): string
    {
        return route('users.show', $this);
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
