<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Token login for the desktop monitoring component. An editor signs the agent
 * in once with their credentials; the agent then holds a Sanctum token.
 */
class AgentAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
            'device'   => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password_hash)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }
        if (! $user->is_active) {
            throw ValidationException::withMessages(['email' => 'Account is not active.']);
        }
        if (! $user->isEditor()) {
            throw ValidationException::withMessages(['email' => 'Only video editors run the monitoring agent.']);
        }

        $token = $user->createToken($data['device'] ?? 'desktop-agent')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => ['id' => $user->user_id, 'name' => $user->name],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['ok' => true]);
    }
}
