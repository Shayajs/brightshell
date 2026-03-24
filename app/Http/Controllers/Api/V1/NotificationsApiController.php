<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationsApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'browser_notifications_enabled' => (bool) $user->browser_notifications_enabled,
                'notifications' => $user->notifications()->latest()->limit(50)->get()->map(fn ($n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'data' => $n->data,
                    'read_at' => $n->read_at?->toIso8601String(),
                    'created_at' => $n->created_at?->toIso8601String(),
                ]),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->user()->update([
            'browser_notifications_enabled' => $request->boolean('browser_notifications_enabled'),
        ]);

        return response()->json(['message' => 'Préférences enregistrées.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifications marquées comme lues.']);
    }
}
