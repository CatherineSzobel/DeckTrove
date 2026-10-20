<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UsernameRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The edit profile form: username, About and an optional new avatar. The checkboxes are read with boolean().
 */
class UpdateProfileRequest extends FormRequest
{
    use UsernameRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => $this->usernameRules($this->user()),
            'about' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
