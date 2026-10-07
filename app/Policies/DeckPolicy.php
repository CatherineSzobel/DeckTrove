<?php

namespace App\Policies;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DeckPolicy
{
    /**
     * Public decks are visible to everyone; private ones only to their owner.
     * Others get a 404 so private decks don't reveal that they exist.
     */
    public function view(?User $user, Deck $deck): Response
    {
        return $deck->is_public || $this->owns($user, $deck)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function update(User $user, Deck $deck): bool
    {
        return $this->owns($user, $deck);
    }

    public function delete(User $user, Deck $deck): bool
    {
        return $this->owns($user, $deck);
    }

    private function owns(?User $user, Deck $deck): bool
    {
        return $user !== null && $user->id === $deck->user_id;
    }
}
