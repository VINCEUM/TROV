<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces the two TROV roles on the server, as required by Chapter 3
 * (functional requirement 1). Usage: ->middleware('role:Owner').
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== $role) {
            // Send people to their own home rather than showing a raw 403.
            return $user
                ? redirect()->route('home')
                : redirect()->route('login');
        }

        return $next($request);
    }
}
