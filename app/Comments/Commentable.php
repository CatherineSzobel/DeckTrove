<?php

namespace App\Comments;

/**
 * Something players can comment on: a profile (User) or a deck (Deck). Its comments are a morphMany on `commentable`.
 */
interface Commentable
{
    /** The user who can delete any comment here: the profile's user, the deck's owner. */
    public function commentOwnerId(): int;

    /** Whether comments can be posted now, and show to other players. */
    public function acceptsComments(): bool;

    /** Where posting or deleting a comment goes back to when there's no previous page (plus #comments). */
    public function commentsFallbackUrl(): string;
}
