<?php

namespace App\Http\Requests;

use App\Models\CollectionItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One card to add to or remove from a collection or wishlist: the series, the card's external id, the list and an
 * optional printing. Errors go in the `collection` bag.
 */
class CollectionCardRequest extends FormRequest
{
    /** The card page sections a counter may come back to without JS. */
    public const ANCHORS = ['in-collection', 'printings'];

    protected $errorBag = 'collection';

    public function rules(): array
    {
        return [
            'game' => ['required', 'string', Rule::in(array_keys(config('series')))],
            'card' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._-]+$/'],
            'list' => ['sometimes', 'string', Rule::in([CollectionItem::OWNED, CollectionItem::WANTED])],
            'printing' => ['nullable', 'string', 'max:64'],
            // Where a no-JS change comes back to on the card page (its collection row or its Printings list).
            'anchor' => ['nullable', 'string', Rule::in(self::ANCHORS)],
        ];
    }

    public function list(): string
    {
        return $this->validated('list') ?? CollectionItem::OWNED;
    }

    public function printing(): string
    {
        return (string) ($this->validated('printing') ?? '');
    }

    /** The card page section a no-JS change returns to, or null (then the card's tile: #card-<id>). */
    public function anchor(): ?string
    {
        return $this->validated('anchor');
    }
}
