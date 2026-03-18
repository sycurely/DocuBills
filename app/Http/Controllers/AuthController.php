<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            // Redirect to dashboard if user has permission, otherwise to clients
            if (has_permission('view_dashboard')) {
                return redirect()->route('dashboard');
            }
            return redirect()->route('clients.index');
        }
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = trim($request->username);
        $password = $request->password;
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $status = 'failure';
        $userId = null;

        // Find user by username
        $user = User::where('username', $username)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            $this->logLoginAttempt($userId, $username, $ipAddress, $userAgent, $status);
            throw ValidationException::withMessages([
                'username' => ['User not found.'],
            ]);
        }

        $userId = $user->id;

        // Verify password
        if (!Hash::check($password, $user->password)) {
            $this->logLoginAttempt($userId, $username, $ipAddress, $userAgent, $status);
            throw ValidationException::withMessages([
                'password' => ['Incorrect password.'],
            ]);
        }

        // Check if suspended
        if ($user->is_suspended) {
            $this->logLoginAttempt($userId, $username, $ipAddress, $userAgent, $status);
            throw ValidationException::withMessages([
                'username' => ['Your account has been suspended. Please contact an administrator.'],
            ]);
        }

        // Login successful
        Auth::login($user);
        $status = 'success';

        // Regenerate session ID for security
        $request->session()->regenerate();
        $sessionId = Session::getId();

        // Track session in database
        UserSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user->id,
                'ip_address' => $ipAddress,
                'last_activity' => now(),
                'terminated_at' => null,
            ]
        );

        // Log successful login
        $this->logLoginAttempt($userId, $username, $ipAddress, $userAgent, $status);

        // Redirect to dashboard if user has permission, otherwise to clients
        if (has_permission('view_dashboard')) {
            return redirect()->intended(route('dashboard'));
        }
        return redirect()->intended(route('clients.index'));
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $sessionId = Session::getId();

        // Mark session as terminated
        if ($user && $sessionId) {
            UserSession::where('session_id', $sessionId)
                ->update([
                    'terminated_at' => now(),
                    'last_activity' => now(),
                ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Log a login attempt.
     */
    private function logLoginAttempt($userId, $username, $ipAddress, $userAgent, $status)
    {
        LoginLog::create([
            'user_id' => $userId,
            'username' => $username,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => $status,
            'created_at' => now(),
        ]);
    }
}
