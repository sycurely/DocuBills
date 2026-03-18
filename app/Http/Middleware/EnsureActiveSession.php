<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSession
{
    /**
     * Ensure the authenticated session is not terminated.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();
        $session = UserSession::where('session_id', $sessionId)->first();

        if ($session && $session->terminated_at !== null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'username' => 'Your session was terminated. Please log in again.',
            ]);
        }

        if ($session) {
            $session->update(['last_activity' => now()]);
        } else {
            UserSession::create([
                'user_id' => Auth::id(),
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'last_activity' => now(),
                'terminated_at' => null,
            ]);
        }

        return $next($request);
    }
}
