<?php

namespace App\Http\Requests;

use App\Comments\Commentable;

/**
 * A comment on a profile, while the profile allows comments.
 */
class StoreProfileCommentRequest extends StoreCommentRequest
{
    protected function target(): Commentable
    {
        return $this->route('user');
    }
}
