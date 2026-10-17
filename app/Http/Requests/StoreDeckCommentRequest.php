<?php

namespace App\Http\Requests;

use App\Comments\Commentable;

/**
 * A comment on a deck, while the deck is public and allows comments.
 */
class StoreDeckCommentRequest extends StoreCommentRequest
{
    protected function target(): Commentable
    {
        return $this->route('deck');
    }
}
