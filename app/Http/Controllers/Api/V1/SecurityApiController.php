<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SecurityApiController extends Controller
{
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }

    public function destroyOtherSessions(Request $request): JsonResponse
    {
        if (config('session.driver') !== 'database') {
            return response()->json(['message' => 'Les sessions ne sont pas stockées en base sur cette instance.'], 422);
        }

        $currentId = session()->getId();

        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $currentId)
            ->delete();

        return response()->json(['message' => 'Les autres sessions ont été déconnectées.']);
    }
}
