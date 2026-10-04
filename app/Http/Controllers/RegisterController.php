<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Self-service sign-up. A new person creates their own TROV account and chooses
 * whether they are an Owner or a Video Editor. Accounts are created directly in
 * the real database (there is no demo seed), then signed in.
 */
class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', Rule::in([User::ROLE_OWNER, User::ROLE_EDITOR])],
        ]);

        $user = User::forceCreate([
            'role'          => $data['role'],
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'is_active'     => true,
        ]);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
