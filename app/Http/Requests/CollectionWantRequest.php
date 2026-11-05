<?php

namespace App\Http\Requests;

/**
 * The card page's heart (POST /collection/want): the card, and whether it should be on the wishlist.
 */
class CollectionWantRequest extends CollectionCardRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'want' => ['required', 'boolean']];
    }

    public function wants(): bool
    {
        return $this->boolean('want');
    }
}
