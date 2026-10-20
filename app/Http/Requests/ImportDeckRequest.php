<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * A deck to import: an uploaded file or a pasted list, in the series' deck format. Whether it holds any cards is
 * checked once it is read (DeckTransferController).
 */
class ImportDeckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['nullable', 'file', 'max:100', 'extensions:ydk,txt,dek'],
            'list' => ['required_without:file', 'nullable', 'string', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return ['list.required_without' => 'Upload a deck file or paste a deck list.'];
    }

    /** The deck list's text, from the file when one was uploaded. */
    public function deckText(): string
    {
        return $this->file('file')?->get() ?? (string) $this->input('list');
    }

    /** The deck name an uploaded file suggests (its name without the extension); null for a pasted list. */
    public function deckName(): ?string
    {
        $file = $this->file('file');

        return $file ? (Str::limit(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 255, '') ?: null) : null;
    }
}
