<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\UserSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginLogsController extends Controller
{
    /**
     * Display login attempts and active sessions.
     */
    public function index(Request $request)
    {
        if (!has_permission('view_login_logs')) {
            abort(403, 'Unauthorized action.');
        }

        $status = strtolower((string) $request->query('status', 'all'));
        if (!in_array($status, ['all', 'success', 'failure'], true)) {
            $status = 'all';
        }

        $search = trim((string) $request->query('search', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $loginLogsQuery = LoginLog::query()
            ->with(['user:id,username,full_name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($status !== 'all') {
            $loginLogsQuery->where('status', $status);
        }

        if ($search !== '') {
            $loginLogsQuery->where(function ($query) use ($search) {
                $query->where('username', 'like', '%' . $search . '%')
                    ->orWhere('ip_address', 'like', '%' . $search . '%');
            });
        }

        if (!empty($dateFrom)) {
            $loginLogsQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $loginLogsQuery->whereDate('created_at', '<=', $dateTo);
        }

        $loginLogs = $loginLogsQuery
            ->paginate(20, ['*'], 'logs_page')
            ->withQueryString();

        $activeSessions = UserSession::query()
            ->with(['user:id,username,full_name'])
            ->whereNull('terminated_at')
            ->orderByDesc('last_activity')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'sessions_page')
            ->withQueryString();

        $stats = [
            'total_attempts_today' => LoginLog::whereDate('created_at', now()->toDateString())->count(),
            'failed_attempts_today' => LoginLog::whereDate('created_at', now()->toDateString())
                ->where('status', 'failure')
                ->count(),
            'active_sessions' => UserSession::whereNull('terminated_at')->count(),
            'successful_attempts_today' => LoginLog::whereDate('created_at', now()->toDateString())
                ->where('status', 'success')
                ->count(),
        ];

        return view('login-logs.index', [
            'loginLogs' => $loginLogs,
            'activeSessions' => $activeSessions,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'canTerminateAnySession' => has_permission('terminate_sessions'),
            'canTerminateOwnSession' => has_permission('terminate_own_session'),
            'currentSessionId' => Session::getId(),
        ]);
    }

    /**
     * Mark a session as terminated.
     */
    public function terminateSession(UserSession $session): RedirectResponse
    {
        $canTerminateAnySession = has_permission('terminate_sessions');
        $canTerminateOwnSession = has_permission('terminate_own_session');

        if (!$canTerminateAnySession && !$canTerminateOwnSession) {
            abort(403, 'Unauthorized action.');
        }

        $isOwnSession = (int) $session->user_id === (int) Auth::id();
        if (!$canTerminateAnySession && !($canTerminateOwnSession && $isOwnSession)) {
            abort(403, 'Unauthorized action.');
        }

        if ($session->terminated_at !== null) {
            return back()->with('error', 'Session is already terminated.');
        }

        $session->update([
            'terminated_at' => now(),
            'last_activity' => now(),
        ]);

        $isCurrentSession = $session->session_id === Session::getId();
        if ($isOwnSession && $isCurrentSession) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Your current session has been terminated.');
        }

        return back()->with('success', 'Session terminated successfully.');
    }
}
