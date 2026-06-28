<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Jenssegers\Agent\Agent;   // optional – for nicer browser/OS names

class ProfileController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    //  DISPLAY PROFILE
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = auth()->user();

        // Quick stats
        $stats = [
            'checkouts_total'  => $user->checkouts()->count(),
            'checkouts_active' => $user->checkouts()->where('status', 'active')->count(),
            'charts_accessed'  => $user->checkouts()->distinct('archived_chart_id')->count('archived_chart_id'),
            'days_active'      => (int) $user->created_at->diffInDays(now()) + 1,
        ];

        // Recent checkouts
        $recentCheckouts = $user->checkouts()
            ->with(['archivedChart.patient'])
            ->latest('checked_out_at')
            ->limit(8)
            ->get();

        // Active sessions from the database
        $currentSessionId = $request->session()->getId();
        $sessions = $this->getSessions($user->id, $currentSessionId);

        return view('profile.index', compact('stats', 'recentCheckouts', 'sessions', 'currentSessionId'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  UPDATE PROFILE INFO
    // ─────────────────────────────────────────────────────────────────────────

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return redirect()->route('profile.index')
            ->with('profile_success', 'Profile updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  UPDATE PASSWORD
    // ─────────────────────────────────────────────────────────────────────────

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.index')
            ->with('password_success', 'Password updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  REVOKE A SINGLE SESSION
    // ─────────────────────────────────────────────────────────────────────────

    public function revokeSession(Request $request, string $id)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $currentSessionId = $request->session()->getId();

        // Safety: never let someone delete their own current session this way
        if ($id === $currentSessionId) {
            return redirect()->route('profile.index')
                ->with('profile_error', 'You cannot revoke your current session here. Use the logout button instead.');
        }

        // Only delete sessions that belong to this user
        DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return redirect()->route('profile.index')
            ->with('profile_success', 'Session revoked successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  REVOKE ALL OTHER SESSIONS
    // ─────────────────────────────────────────────────────────────────────────

    public function logoutOtherSessions(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $currentSessionId = $request->session()->getId();

        // Delete every session for this user except the current one
        DB::table('sessions')
            ->where('user_id', auth()->id())
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return redirect()->route('profile.index')
            ->with('profile_success', 'All other sessions have been signed out.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPER: build sessions list
    // ─────────────────────────────────────────────────────────────────────────

    private function getSessions(int $userId, string $currentSessionId): \Illuminate\Support\Collection
    {
        return DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $agent = $this->parseAgent($session->user_agent ?? '');

                return (object) [
                    'id'            => $session->id,
                    'is_current'    => $session->id === $currentSessionId,
                    'ip_address'    => $session->ip_address ?? 'Unknown',
                    'user_agent'    => $session->user_agent ?? '',
                    'browser'       => $agent['browser'],
                    'platform'      => $agent['platform'],
                    'device_type'   => $agent['device_type'],
                    'device_icon'   => $agent['device_icon'],
                    'last_active'   => \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    'last_active_ts'=> $session->last_activity,
                ];
            });
    }

    /**
     * Parse a user-agent string without requiring the Jenssegers package.
     * Returns browser name, platform, device type, and an icon class.
     */
    private function parseAgent(string $ua): array
    {
        // ── Browser ──────────────────────────────────────────────────────────
        $browser = 'Unknown Browser';
        $browsers = [
            'Edg'           => 'Edge',
            'OPR'           => 'Opera',
            'Opera'         => 'Opera',
            'SamsungBrowser'=> 'Samsung Browser',
            'UCBrowser'     => 'UC Browser',
            'YaBrowser'     => 'Yandex Browser',
            'Firefox'       => 'Firefox',
            'Chrome'        => 'Chrome',
            'Safari'        => 'Safari',
            'MSIE'          => 'Internet Explorer',
            'Trident'       => 'Internet Explorer',
        ];
        foreach ($browsers as $key => $name) {
            if (str_contains($ua, $key)) { $browser = $name; break; }
        }

        // ── Platform / OS ────────────────────────────────────────────────────
        $platform = 'Unknown OS';
        $platforms = [
            'Windows NT 10'  => 'Windows 10/11',
            'Windows NT 6.3' => 'Windows 8.1',
            'Windows NT 6.1' => 'Windows 7',
            'Windows'        => 'Windows',
            'iPhone'         => 'iOS (iPhone)',
            'iPad'           => 'iOS (iPad)',
            'Android'        => 'Android',
            'Macintosh'      => 'macOS',
            'Linux'          => 'Linux',
            'CrOS'           => 'ChromeOS',
        ];
        foreach ($platforms as $key => $name) {
            if (str_contains($ua, $key)) { $platform = $name; break; }
        }

        // ── Device type ──────────────────────────────────────────────────────
        $isMobile = preg_match('/Mobile|Android|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua);
        $isTablet = preg_match('/Tablet|iPad/i', $ua);

        if ($isTablet) {
            $device_type = 'Tablet';
            $device_icon = 'fa-tablet-alt';
        } elseif ($isMobile) {
            $device_type = 'Mobile';
            $device_icon = 'fa-mobile-alt';
        } else {
            $device_type = 'Desktop';
            $device_icon = 'fa-desktop';
        }

        return compact('browser', 'platform', 'device_type', 'device_icon');
    }
}