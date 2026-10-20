<?php

namespace App\Http\Requests;

use App\Enums\BulkDeckAction;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the Edit profile page's "Your decks" form: some of your own decks (`decks[]`) and what to do with them
 * (`action`, from the clicked button). Errors go to the `bulkDecks` bag and back to that panel.
 */
class BulkDeckRequest extends FormRequest
{
    protected $errorBag = 'bulkDecks';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // One check for the whole list, so the message shows under the list rather than per id.
            'decks' => ['bail', 'required', 'array', 'min:1', function (string $attribute, mixed $ids, Closure $fail) {
                $ids = array_unique(array_filter($ids, 'is_numeric'));

                if (count($ids) !== count($this->input('decks')) || $this->user()->decks()->whereKey($ids)->count() !== count($ids)) {
                    $fail('Select only your own decks.');
                }
            }],
            'action' => ['required', Rule::enum(BulkDeckAction::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'decks.required' => 'Select at least one deck.',
            'decks.min' => 'Select at least one deck.',
        ];
    }

    public function action(): BulkDeckAction
    {
        return $this->enum('action', BulkDeckAction::class);
    }

    /**
     * @return list<int>
     */
    public function deckIds(): array
    {
        return array_map('intval', $this->validated('decks'));
    }

    protected function getRedirectUrl(): string
    {
        return route('profile.edit').'#your-decks';
    }
}
