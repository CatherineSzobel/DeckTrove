<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterUserController extends Controller
{

    public function create()
    {
        return view('auth.register');
    }
    public function store()
    {
        $validatedAttributes = request()->validate([
            'username' => ['required', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(6), 'confirmed'],
        ]);

        $validatedAttributes['password'] = bcrypt($validatedAttributes['password']);

        $user = User::create($validatedAttributes);

        Auth::login($user);

        return redirect(url()->previous());
    }
}
