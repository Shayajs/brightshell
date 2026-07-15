<?php

namespace App\Http\Controllers\BrightShield;

use App\Services\BrightShield\UserClaimsBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API BrightShield sur api.* — lecture des données autorisées par le jeton
 * Passport (scopes). Chaque endpoint ne renvoie que ce que le scope permet.
 */
class ApiMeController
{
    public function __construct(
        private readonly UserClaimsBuilder $claimsBuilder,
    ) {
    }

    /** Toutes les données couvertes par les scopes du jeton. */
    public function show(Request $request): JsonResponse
    {
        return response()->json(
            $this->claimsBuilder->build($request->user(), $this->tokenScopes($request)),
        );
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->scoped($request, 'profile');
    }

    public function email(Request $request): JsonResponse
    {
        return $this->scoped($request, 'email');
    }

    public function phone(Request $request): JsonResponse
    {
        return $this->scoped($request, 'phone');
    }

    public function roles(Request $request): JsonResponse
    {
        return $this->scoped($request, 'roles');
    }

    public function account(Request $request): JsonResponse
    {
        return $this->scoped($request, 'account');
    }

    private function scoped(Request $request, string $scope): JsonResponse
    {
        $user = $request->user();

        if (! $user->tokenCan($scope)) {
            return response()->json([
                'message' => "Le jeton n'a pas le scope « {$scope} ».",
            ], 403);
        }

        $claims = $this->claimsBuilder->build($user, [$scope]);
        unset($claims['sub']);

        return response()->json($claims);
    }

    /**
     * @return list<string>
     */
    private function tokenScopes(Request $request): array
    {
        $token = $request->user()->currentAccessToken();

        if ($token === null) {
            return [];
        }

        return collect(config('brightshield.scopes', []))
            ->keys()
            ->filter(fn (string $scope) => $token->can($scope))
            ->values()
            ->all();
    }
}
