<?php

namespace App\Enums;

/**
 * What the "Your decks" bulk form (edit profile) can do to the selected decks.
 */
enum BulkDeckAction: string
{
    case Public = 'public';
    case Private = 'private';
    case CommentsOn = 'comments_on';
    case CommentsOff = 'comments_off';
    case Delete = 'delete';

    /** How the flash message ends: "3 decks made public." */
    public function doneLabel(): string
    {
        return match ($this) {
            self::Public => 'made public',
            self::Private => 'made private',
            self::CommentsOn => 'opened to comments',
            self::CommentsOff => 'closed to comments',
            self::Delete => 'deleted',
        };
    }
}
