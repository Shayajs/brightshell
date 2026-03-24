<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationPreferencesController extends Controller
{
    public function edit(): View
    {
        return view('portals.settings.notifications', [
            'user' => auth()->user(),
            'notifications' => auth()->user()->notifications()->latest()->limit(25)->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->user()->update([
            'browser_notifications_enabled' => $request->boolean('browser_notifications_enabled'),
        ]);

        return redirect()
            ->route('portals.settings.notifications.edit')
            ->with('success', 'Préférences de notification enregistrées.');
    }

    public function bridge(): View
    {
        return view('portals.settings.notifications-bridge');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'Notifications marquées comme lues.');
    }
}
