<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
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
     * Handle public trial signup from the landing page.
     */
    public function signupTrial(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $email = strtolower(trim($validated['email']));

        $existingUser = User::where('email', $email)->whereNull('deleted_at')->first();
        $trashedUser = User::onlyTrashed()->where('email', $email)->first();

        if ($existingUser) {
            $sent = EmailService::sendRenderedReminderNow(
                toEmail: $existingUser->email,
                toName: $existingUser->full_name ?: $existingUser->name ?: $existingUser->username,
                subject: 'Your DocuBills account is already active',
                body: $this->buildExistingAccountEmail($existingUser)
            );

            if (!$sent) {
                return back()->withInput()->withErrors([
                    'email' => 'This email is already registered, but we could not send the account email. Please check the mail settings.',
                ]);
            }

            return back()->withInput()->with('success', 'This email is already registered. Login details have been sent to the email address.');
        }

        if ($trashedUser) {
            $trashedUser->forceDelete();
        }

        $baseUsername = Str::lower((string) Str::before($email, '@'));
        $baseUsername = preg_replace('/[^a-z0-9._-]/', '', $baseUsername) ?: 'user';
        $username = $baseUsername;
        $suffix = 1;

        while (User::withTrashed()->where('username', $username)->exists()) {
            $username = $baseUsername . $suffix;
            $suffix++;
        }

        $plainPassword = Str::password(12, true, true, false, false);
        $roleId = Role::where('name', 'manager')->value('id')
            ?? Role::where('name', 'assistant')->value('id')
            ?? Role::where('name', 'viewer')->value('id')
            ?? Role::orderBy('id')->value('id');
        $displayName = Str::title(str_replace(['.', '_', '-'], ' ', $baseUsername));

        $user = User::create([
            'username' => $username,
            'name' => $displayName,
            'full_name' => $displayName,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'role_id' => $roleId,
            'workspace_owner_id' => null,
            'is_suspended' => false,
        ]);

        $user->forceFill(['workspace_owner_id' => $user->id])->save();

        try {
            $sent = EmailService::sendRenderedReminderNow(
                toEmail: $user->email,
                toName: $user->full_name ?: $user->name ?: $user->username,
                subject: 'Your DocuBills account details',
                body: $this->buildTrialSignupEmail($user->email, $username, $plainPassword)
            );

            if (!$sent) {
                throw new \RuntimeException('Mail transport returned false.');
            }
        } catch (\Throwable $e) {
            $user->delete();

            report($e);

            return back()->withInput()->withErrors([
                'email' => 'The account was created, but we could not send the confirmation email. Please check the mail settings and try again.',
            ]);
        }

        return back()->with('success', 'Your account has been created. Login details have been sent to your email address.');
    }

    /**
     * Handle public company signup from the landing page.
     */
    public function signupCompany(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'company_email' => 'required|string|email|max:255',
        ]);

        $companyName = trim($validated['company_name']);
        $fullName = trim($validated['full_name']);
        $email = strtolower(trim($validated['company_email']));

        $existingUser = User::where('email', $email)->whereNull('deleted_at')->first();
        $trashedUser = User::onlyTrashed()->where('email', $email)->first();

        if ($existingUser) {
            $sent = EmailService::sendRenderedReminderNow(
                toEmail: $existingUser->email,
                toName: $existingUser->full_name ?: $existingUser->name ?: $existingUser->username,
                subject: 'Your DocuBills company account is already active',
                body: $this->buildExistingAccountEmail($existingUser)
            );

            if (!$sent) {
                return back()->withInput()->withErrors([
                    'company_email' => 'This company email is already registered, but we could not send the account email. Please check the mail settings.',
                ]);
            }

            return back()->withInput()->with('success', 'This company email is already registered. Login details have been sent to the email address.');
        }

        if ($trashedUser) {
            $trashedUser->forceDelete();
        }

        $baseUsername = Str::lower((string) Str::before($email, '@'));
        $baseUsername = preg_replace('/[^a-z0-9._-]/', '', $baseUsername) ?: 'companyadmin';
        $username = $baseUsername;
        $suffix = 1;

        while (User::withTrashed()->where('username', $username)->exists()) {
            $username = $baseUsername . $suffix;
            $suffix++;
        }

        $plainPassword = Str::password(12, true, true, false, false);
        $roleId = Role::where('name', 'admin')->value('id')
            ?? Role::where('name', 'super_admin')->value('id')
            ?? Role::where('name', 'manager')->value('id')
            ?? Role::orderBy('id')->value('id');

        $user = User::create([
            'username' => $username,
            'name' => $fullName,
            'full_name' => $fullName,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'role_id' => $roleId,
            'workspace_owner_id' => null,
            'is_suspended' => false,
        ]);

        $user->forceFill(['workspace_owner_id' => $user->id])->save();

        try {
            $sent = EmailService::sendRenderedReminderNow(
                toEmail: $user->email,
                toName: $user->full_name ?: $user->name ?: $user->username,
                subject: 'Your DocuBills company admin account details',
                body: $this->buildCompanySignupEmail($companyName, $fullName, $user->email, $username, $plainPassword)
            );

            if (!$sent) {
                throw new \RuntimeException('Mail transport returned false.');
            }
        } catch (\Throwable $e) {
            $user->delete();

            report($e);

            return back()->withInput()->withErrors([
                'company_email' => 'The company account was created, but we could not send the confirmation email. Please check the mail settings and try again.',
            ]);
        }

        return back()->with('success', 'The company account has been created. Admin login details have been sent to the email address.');
    }

    private function buildTrialSignupEmail(string $email, string $username, string $plainPassword): string
    {
        $loginUrl = route('login');
        $safeEmail = e($email);
        $safeUsername = e($username);
        $safePassword = e($plainPassword);
        $safeLoginUrl = e($loginUrl);

        return <<<HTML
<div style="font-family: Arial, sans-serif; color: #1f2544; line-height: 1.6;">
  <h2 style="margin-bottom: 16px;">Welcome to DocuBills</h2>
  <p>Your free trial account is ready.</p>
  <p><strong>Email:</strong> {$safeEmail}<br><strong>Username:</strong> {$safeUsername}<br><strong>Password:</strong> {$safePassword}</p>
  <p>You can sign in here: <a href="{$safeLoginUrl}">{$safeLoginUrl}</a></p>
  <p>For security, please log in and change your password after your first sign-in.</p>
</div>
HTML;
    }

    private function buildExistingAccountEmail(User $user): string
    {
        $loginUrl = route('login');
        $safeName = e($user->full_name ?: $user->name ?: $user->username);
        $safeEmail = e($user->email);
        $safeUsername = e($user->username);
        $safeLoginUrl = e($loginUrl);

        return <<<HTML
<div style="font-family: Arial, sans-serif; color: #1f2544; line-height: 1.6;">
  <h2 style="margin-bottom: 16px;">Your DocuBills account</h2>
  <p>Hello <strong>{$safeName}</strong>,</p>
  <p>An account already exists for this email.</p>
  <p><strong>Email:</strong> {$safeEmail}<br><strong>Username:</strong> {$safeUsername}</p>
  <p>You can sign in here: <a href="{$safeLoginUrl}">{$safeLoginUrl}</a></p>
  <p>If you forgot your password, please contact the administrator to reset it.</p>
</div>
HTML;
    }

    private function buildCompanySignupEmail(string $companyName, string $fullName, string $email, string $username, string $plainPassword): string
    {
        $loginUrl = route('login');
        $safeCompanyName = e($companyName);
        $safeFullName = e($fullName);
        $safeEmail = e($email);
        $safeUsername = e($username);
        $safePassword = e($plainPassword);
        $safeLoginUrl = e($loginUrl);

        return <<<HTML
<div style="font-family: Arial, sans-serif; color: #1f2544; line-height: 1.6;">
  <h2 style="margin-bottom: 16px;">Welcome to DocuBills</h2>
  <p>Hello <strong>{$safeFullName}</strong>,</p>
  <p>Your company workspace request for <strong>{$safeCompanyName}</strong> has been created and your account is set up as an Admin.</p>
  <p><strong>Email:</strong> {$safeEmail}<br><strong>Username:</strong> {$safeUsername}<br><strong>Password:</strong> {$safePassword}</p>
  <p>You can sign in here: <a href="{$safeLoginUrl}">{$safeLoginUrl}</a></p>
  <p>For security, please log in and change your password after your first sign-in.</p>
</div>
HTML;
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
