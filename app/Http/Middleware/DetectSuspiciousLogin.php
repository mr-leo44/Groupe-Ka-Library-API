<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Notifications\NewDeviceLoginNotification;
use Symfony\Component\HttpFoundation\Response;

class DetectSuspiciousLogin
{
    /**
     * Detect logins from new IP addresses or devices.
     * Logs the event and optionally sends email notification.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $currentIp = $request->ip();
        $userAgent = $request->userAgent();
        $cacheKey = "known_devices:{$user->id}";

        // Get known devices from cache
        $knownDevices = Cache::get($cacheKey, []);

        // Create unique fingerprint for this device/IP combination
        $deviceFingerprint = md5($currentIp . $userAgent);

        // Check if this is a new device
        if (!in_array($deviceFingerprint, $knownDevices)) {
            
            // Log the new device detection
            activity()
                ->causedBy($user)
                ->withProperties([
                    'ip' => $currentIp,
                    'user_agent' => $userAgent,
                    'device_fingerprint' => $deviceFingerprint,
                    'timestamp' => now()->toDateTimeString()
                ])
                ->log('New device/IP detected');

            // Send email notification (uncomment to enable)
            // try {
            //     $user->notify(new NewDeviceLoginNotification($currentIp, $userAgent));
            // } catch (\Exception $e) {
            //     \Log::warning('Failed to send new device notification: ' . $e->getMessage());
            // }

            // Add this device to known devices (cache for 90 days)
            $knownDevices[] = $deviceFingerprint;
            Cache::put($cacheKey, $knownDevices, now()->addDays(90));
        }

        return $next($request);
    }
}