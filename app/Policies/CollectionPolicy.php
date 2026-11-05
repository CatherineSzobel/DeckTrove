<?php

namespace App\Policies;

use App\Models\User;

/** Who may see a user's collection or wishlist: the user, and everyone while its toggle is on. */
class CollectionPolicy
{
    public function viewCollection(?User $viewer, User $owner): bool
    {
        return $viewer?->is($owner) || $owner->collection_public;
    }

    public function viewWishlist(?User $viewer, User $owner): bool
    {
        return $viewer?->is($owner) || $owner->wishlist_public;
    }
}
