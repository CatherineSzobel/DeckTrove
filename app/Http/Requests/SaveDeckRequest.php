<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates deck builder submissions (create and update).
 *
 * `cards` arrives as a JSON string: {"main": [{"id": "..."}, ...], "side": [...]}.
 * Only card ids are used; names and images are looked up server side.
 */
class SaveDeckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function game(): string
    {
        return $this->route('series') ?? $this->route('deck')->game;
    }

    protected function prepareForValidation(): void
    {
        $cards = $this->input('cards');

        $this->merge([
            'cards' => is_string($cards) ? (json_decode($cards, true) ?? $cards) : ($cards ?? []),
            'is_public' => $this->boolean('is_public'),
        ]);
    }

    public function rules(): array
    {
        $zones = array_keys(config("series.{$this->game()}.deck.zones"));

        return [
            'deck_title' => ['nullable', 'string', 'max:255'],
            'deck_description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'url:https', 'max:2048'],
            'is_public' => ['boolean'],
            'cards' => ['present', 'array:'.implode(',', $zones)],
            'cards.*' => ['array', 'max:100'],
            'cards.*.*.id' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._-]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'cards.array' => 'The deck contains a zone this game does not have.',
            'cards.*.*.id.*' => 'The deck contains an invalid card.',
        ];
    }

    /**
     * Card counts per zone: ['main' => ['46986414' => 3, ...], ...].
     */
    public function cardCounts(): array
    {
        return collect($this->validated('cards'))
            ->map(fn (array $cards) => collect($cards)->countBy(fn ($card) => (string) $card['id'])->all())
            ->all();
    }
}
