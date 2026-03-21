<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $recentNotifications = $user->notifications()->latest()->limit(8)->get();
        $unreadNotificationsCount = $user->unreadNotifications()->count();

        $otherSessionsCount = 0;
        if (config('session.driver') === 'database') {
            $otherSessionsCount = (int) DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', session()->getId())
                ->count();
        }

        return view('portals.settings.dashboard', [
            'user' => $user,
            'recentNotifications' => $recentNotifications,
            'unreadNotificationsCount' => $unreadNotificationsCount,
            'otherSessionsCount' => $otherSessionsCount,
        ]);
    }
}
