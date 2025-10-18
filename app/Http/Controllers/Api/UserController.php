<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * @tags User Profile
 */
class UserController extends Controller
{
    /**
     * Get authenticated user profile
     * 
     * Returns the current user's profile with their roles, city, and clan information.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "User profile retrieved",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "email_verified_at": "2024-01-15T10:30:00.000000Z",
     *     "city": {"id": 1, "name": "Kinshasa"},
     *     "clan": {"id": 1, "name": "Clan Saint Pierre"},
     *     "roles": [{"name": "member"}]
     *   }
     * }
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
     * 
     * Update the authenticated user's name, city, or clan.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Profile updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe Updated",
     *     "email": "john@example.com"
     *   }
     * }
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
     * 
     * Change the authenticated user's password. 
     * All other active sessions will be logged out for security.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Password changed successfully. All other sessions have been logged out."
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "message": "Current password is incorrect"
     * }
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

        if (!Hash::check($validated['current_password'], $user->password)) {
            return ApiResponse::error('Current password is incorrect', null, 400);
        }

        if (Hash::check($validated['password'], $user->password)) {
            return ApiResponse::error('New password must be different from current password', null, 400);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        $currentToken = $user->currentAccessToken();
        $user->tokens()->where('id', '!=', $currentToken->id)->delete();

        activity()
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip()])
            ->log('Password changed');

        return ApiResponse::success('Password changed successfully. All other sessions have been logged out.');
    }

    /**
     * Get all active sessions
     * 
     * List all active authentication tokens (devices) for the current user.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Active sessions retrieved",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "mobile-app",
     *       "last_used_at": "2024-01-15T10:30:00.000000Z",
     *       "created_at": "2024-01-10T08:00:00.000000Z",
     *       "is_current": true
     *     }
     *   ]
     * }
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
     * 
     * Logout from a specific device by revoking its token.
     * Cannot revoke the current session (use /logout instead).
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Session revoked successfully"
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "message": "Cannot revoke current session. Use logout instead."
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "Session not found"
     * }
     */
    public function revokeSession(Request $request, string $tokenId)
    {
        $user = $request->user();
        
        $token = $user->tokens()->find($tokenId);

        if (!$token) {
            return ApiResponse::error('Session not found', null, 404);
        }

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
     * List all users
     * 
     * Get paginated list of all users with their roles and locations.
     * Admin only.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Users list retrieved",
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "john@example.com",
     *         "roles": [{"name": "member"}]
     *       }
     *     ],
     *     "total": 50
     *   }
     * }
     * 
     * @response 403 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
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
     * Show user details
     * 
     * Get detailed information about a specific user.
     * Admin only.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "User details retrieved",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "roles": [{"name": "member"}],
     *     "permissions": []
     *   }
     * }
     */
    public function show(User $user)
    {
        return ApiResponse::success(
            'User details retrieved',
            $user->load(['city', 'clan', 'roles', 'permissions'])
        );
    }

    /**
     * Update user role
     * 
     * Change a user's role (admin, manager, or member).
     * Cannot change your own role.
     * Admin only.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Role updated successfully",
     *   "data": {
     *     "id": 2,
     *     "name": "Jane Doe",
     *     "roles": [{"name": "manager"}]
     *   }
     * }
     * 
     * @response 403 {
     *   "success": false,
     *   "message": "Cannot change your own role"
     * }
     */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|in:admin,manager,member'
        ]);

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
     * Delete user
     * 
     * Soft delete a user account. Can be restored later.
     * Cannot delete your own account.
     * Admin only.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "User deleted successfully"
     * }
     * 
     * @response 403 {
     *   "success": false,
     *   "message": "Cannot delete your own account"
     * }
     */
    public function destroy(Request $request, User $user)
    {
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
     * Restore deleted user
     * 
     * Restore a soft-deleted user account.
     * Admin only.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "User restored successfully",
     *   "data": {
     *     "id": 5,
     *     "name": "Restored User",
     *     "email": "restored@example.com"
     *   }
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "message": "User is not deleted"
     * }
     */
    public function restore(string $userId)
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