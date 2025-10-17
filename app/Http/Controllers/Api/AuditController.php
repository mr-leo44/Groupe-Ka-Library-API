<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class UserController extends Controller
{
    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        return ApiResponse::success(
            'User profile retrieved',
            $request->user()->load(['city', 'clan', 'roles'])
        );
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'city_id' => 'nullable|exists:cities,id',
            'clan_id' => 'nullable|exists:clans,id',
        ]);

        $user->update($validated);

        activity()
            ->causedBy($user)
            ->withProperties(['updated_fields' => array_keys($validated)])
            ->log('Profile updated');

        return ApiResponse::success('Profile updated successfully', $user->fresh());
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return ApiResponse::error('Current password is incorrect', null, 400);
        }

        // Prevent reusing the same password
        if (Hash::check($validated['password'], $user->password)) {
            return ApiResponse::error('New password must be different from current password', null, 400);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        // Revoke all other tokens except current one
        $currentToken = $user->currentAccessToken();
        $user->tokens()->where('id', '!=', $currentToken->id)->delete();

        activity()
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip()])
            ->log('Password changed');

        return ApiResponse::success('Password changed successfully. All other sessions have been logged out.');
    }

    /**
     * Get all active sessions (tokens)
     */
    public function sessions(Request $request)
    {
        $user = $request->user();
        
        $tokens = $user->tokens()->get()->map(function ($token) use ($user) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at,
                'is_current' => $token->id === $user->currentAccessToken()->id,
            ];
        });

        return ApiResponse::success('Active sessions retrieved', $tokens);
    }

    /**
     * Revoke a specific session
     */
    public function revokeSession(Request $request, $tokenId)
    {
        $user = $request->user();
        
        $token = $user->tokens()->find($tokenId);

        if (!$token) {
            return ApiResponse::error('Session not found', null, 404);
        }

        // Prevent revoking current session
        if ($token->id === $user->currentAccessToken()->id) {
            return ApiResponse::error('Cannot revoke current session. Use logout instead.', null, 400);
        }

        $token->delete();

        activity()
            ->causedBy($user)
            ->withProperties(['token_id' => $tokenId, 'token_name' => $token->name])
            ->log('Session revoked');

        return ApiResponse::success('Session revoked successfully');
    }

    /**
     * List all users (Admin only)
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        
        $users = User::with(['city', 'clan', 'roles'])
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->has('role'), function ($query) use ($request) {
                $query->role($request->role);
            })
            ->latest()
            ->paginate($perPage);

        return ApiResponse::success('Users list retrieved', $users);
    }

    /**
     * Show user details (Admin only)
     */
    public function show(User $user)
    {
        return ApiResponse::success(
            'User details retrieved',
            $user->load(['city', 'clan', 'roles', 'permissions'])
        );
    }

    /**
     * Update user role (Admin only)
     */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|in:admin,manager,member'
        ]);

        // Prevent self-demotion
        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return ApiResponse::error('Cannot change your own role', null, 403);
        }

        $oldRole = $user->roles->pluck('name')->first();
        $user->syncRoles([$validated['role']]);

        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties([
                'old_role' => $oldRole,
                'new_role' => $validated['role']
            ])
            ->log('User role updated');

        return ApiResponse::success('Role updated successfully', $user->load('roles'));
    }

    /**
     * Delete user (Admin only)
     */
    public function destroy(Request $request, User $user)
    {
        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return ApiResponse::error('Cannot delete your own account', null, 403);
        }

        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties(['deleted_user_email' => $user->email])
            ->log('User deleted');

        $user->delete();

        return ApiResponse::success('User deleted successfully');
    }

    /**
     * Restore deleted user (Admin only)
     */
    public function restore($userId)
    {
        $user = User::withTrashed()->findOrFail($userId);

        if (!$user->trashed()) {
            return ApiResponse::error('User is not deleted', null, 400);
        }

        $user->restore();

        activity()
            ->causedBy(request()->user())
            ->performedOn($user)
            ->log('User restored');

        return ApiResponse::success('User restored successfully', $user);
    }
}