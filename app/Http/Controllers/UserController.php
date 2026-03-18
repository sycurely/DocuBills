<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        if (!has_permission('manage_users_page')) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::with('role')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $roles = Role::orderBy('id')->get();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = time() . '_' . $avatar->getClientOriginalName();
            $path = $avatar->storeAs('avatars', $filename, 'uploads');
            $data['avatar'] = '/storage/uploads/' . $path;
        }

        User::create($data);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user (for view modal).
     */
    public function show(User $user, Request $request)
    {
        $isSelfProfile = Auth::id() === $user->id;
        if (!$isSelfProfile && !has_permission('manage_users_page')) {
            abort(403, 'Unauthorized action.');
        }

        $user->load('role');

        if ($request->boolean('modal') || $request->ajax()) {
            return view('users.show-modal', compact('user'));
        }

        return view('users.show', compact('user'));
    }

    /**
     * Get user data for edit modal (AJAX).
     */
    public function edit(User $user)
    {
        if (!has_permission('edit_user')) {
            abort(403, 'Unauthorized action.');
        }

        $user->load('role');
        $roles = Role::orderBy('id')->get();
        $canAssignRoles = Auth::user()->role?->name === 'super_admin' || has_permission('assign_roles');

        return view('users.edit', compact('user', 'roles', 'canAssignRoles'));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // Update password only if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                $oldPath = str_replace('/storage/uploads/', '', $user->avatar);
                Storage::disk('uploads')->delete($oldPath);
            }

            $avatar = $request->file('avatar');
            $filename = $user->id . '_' . time() . '_' . $avatar->getClientOriginalName();
            $path = $avatar->storeAs('avatars', $filename, 'uploads');
            $data['avatar'] = '/storage/uploads/' . $path;
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user (soft delete).
     */
    public function destroy(User $user)
    {
        if (!has_permission('delete_user')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent deleting own account
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user suspension status.
     */
    public function toggleSuspend(User $user)
    {
        if (!has_permission('suspend_users')) {
            abort(403, 'Unauthorized action.');
        }

        $user->update([
            'is_suspended' => !$user->is_suspended,
        ]);

        $status = $user->is_suspended ? 'suspended' : 'activated';
        return redirect()->route('users.index')->with('success', "User {$status} successfully.");
    }

    /**
     * Check if username is available (AJAX).
     */
    public function checkUsername(Request $request): JsonResponse
    {
        $username = trim($request->query('username', $request->input('username', '')));
        $userId = (int) ($request->query('user_id', $request->input('user_id', 0)));

        if (empty($username)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username cannot be empty.',
            ]);
        }

        $exists = User::where('username', $username)
            ->where('id', '!=', $userId)
            ->whereNull('deleted_at')
            ->exists();

        return response()->json([
            'status' => $exists ? 'taken' : 'available',
            'message' => $exists ? 'Username is already taken.' : 'Username is available.',
        ]);
    }

    /**
     * Check if email is available (AJAX).
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $email = trim($request->query('email', $request->input('email', '')));
        $userId = (int) ($request->query('user_id', $request->input('user_id', 0)));

        if (empty($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email cannot be empty.',
            ]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email format.',
            ]);
        }

        $exists = User::where('email', $email)
            ->where('id', '!=', $userId)
            ->whereNull('deleted_at')
            ->exists();

        return response()->json([
            'status' => $exists ? 'taken' : 'available',
            'message' => $exists ? 'Email is already taken.' : 'Email is available.',
        ]);
    }

    /**
     * Update user password (AJAX).
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:new_password',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }
}
