<?php

namespace App\Http\Requests\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * The username rules, shared by registering and editing the profile (which ignores the user's own name).
 */
trait UsernameRules
{
    protected function usernameRules(?User $ignore = null): array
    {
        return ['required', 'string', 'alpha_dash', 'min:3', 'max:30', Rule::unique('users', 'username')->ignore($ignore)];
    }
}
