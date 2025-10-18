<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    /**
     * List all audits (Admin only)
     * Audits = Model changes (created, updated, deleted)
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        
        $query = Audit::with(['user', 'auditable'])
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by event type
        if ($request->has('event')) {
            $query->where('event', $request->event);
        }

        // Filter by model type
        if ($request->has('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $audits = $query->paginate($perPage);

        return ApiResponse::success('Audits retrieved successfully', $audits);
    }

    /**
     * Get audits for a specific user
     * 
     * View all model changes made by a specific user.
     * Admin only.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Audits for user John Doe",
     *   "data": {
     *     "data": [...]
     *   }
     * }
     */
    public function userAudits(User $user)
    {
        $audits = Audit::where('user_id', $user->id)
            ->with('auditable')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return ApiResponse::success("Audits for user {$user->name}", $audits);
    }

    /**
     * List all activity logs (Admin only)
     * 
     * Get paginated list of user actions (login, logout, etc.).
     * Activities = User actions (login, logout, password changes, etc.)
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Activity logs retrieved successfully",
     *   "data": {
     *     "data": [
     *       {
     *         "description": "User logged in",
     *         "causer": {"name": "John Doe"},
     *         "properties": {"ip": "127.0.0.1"},
     *         "created_at": "2024-01-15T10:30:00.000000Z"
     *       }
     *     ]
     *   }
     * }
     */
    public function activityLogs(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        
        $query = Activity::with('causer')
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', User::class);
        }

        // Filter by description/log name
        if ($request->has('description')) {
            $query->where('description', 'like', "%{$request->description}%");
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $activities = $query->paginate($perPage);

        return ApiResponse::success('Activity logs retrieved successfully', $activities);
    }

    /**
     * Get activity logs for a specific user
     * 
     * View all actions performed by a specific user.
     * Admin only.
     * 
     * @authenticated
     */
    public function userActivityLogs(User $user)
    {
        $activities = Activity::where('causer_id', $user->id)
            ->where('causer_type', User::class)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return ApiResponse::success("Activity logs for user {$user->name}", $activities);
    }

    /**
     * Get security events (logins, logouts, failed attempts)
     * 
     * Filter activity logs to show only security-related events.
     * Admin only.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Security events retrieved",
     *   "data": {
     *     "data": [
     *       {
     *         "description": "Failed login attempt",
     *         "properties": {"email": "test@example.com", "ip": "192.168.1.1"}
     *       }
     *     ]
     *   }
     * }
     */
    public function securityEvents(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        
        $securityDescriptions = [
            'User logged in',
            'User logged out',
            'Failed login attempt',
            'User logged in via google',
            'User logged in via apple',
            'Password changed',
            'Password reset successfully',
            'New device/IP detected',
        ];

        $activities = Activity::whereIn('description', $securityDescriptions)
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ApiResponse::success('Security events retrieved', $activities);
    }

    /**
     * Get statistics for dashboard
     * 
     * Get aggregate statistics about users and activity.
     * Admin only.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Statistics retrieved",
     *   "data": {
     *     "total_users": 50,
     *     "verified_users": 45,
     *     "logins_today": 12,
     *     "failed_logins_today": 3
     *   }
     * }
     */
    public function statistics()
    {
        $stats = [
            'total_users' => User::count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
            'social_users' => User::whereNotNull('provider')->count(),
            'deleted_users' => User::onlyTrashed()->count(),
            'logins_today' => Activity::where('description', 'User logged in')
                ->whereDate('created_at', today())
                ->count(),
            'failed_logins_today' => Activity::where('description', 'Failed login attempt')
                ->whereDate('created_at', today())
                ->count(),
            'registrations_today' => Activity::where('description', 'User registered')
                ->whereDate('created_at', today())
                ->count(),
        ];

        return ApiResponse::success('Statistics retrieved', $stats);
    }
}
