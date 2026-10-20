<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Deleting the account, confirmed with the password (in its own error bag, see UpdatePasswordRequest).
 */
class DeleteAccountRequest extends FormRequest
{
    protected $errorBag = 'deleteAccount';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['password' => ['required', 'current_password']];
    }
}
