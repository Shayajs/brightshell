<?php

namespace App\Http\Controllers\BrightShield;

use App\Services\BrightShield\UserClaimsBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserInfoController
{
    public function __invoke(Request $request, UserClaimsBuilder $claimsBuilder): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $scopes = collect(config('brightshield.scopes', []))
            ->keys()
            ->filter(fn (string $scope) => $user->tokenCan($scope))
            ->values()
            ->all();

        return response()->json($claimsBuilder->build($user, $scopes));
    }
}
