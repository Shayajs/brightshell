<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicBusinessProfileResource;
use App\Models\BusinessProfile;
use Illuminate\Http\JsonResponse;

class PublicBusinessProfileController extends Controller
{
    /**
     * GET /v1/entreprise — informations publiques (non sensibles), mises à jour en temps réel depuis l’admin.
     */
    public function show(): JsonResponse
    {
        $profile = BusinessProfile::singleton();

        return (new PublicBusinessProfileResource($profile))
            ->response()
            ->header('Cache-Control', 'public, max-age=60');
    }
}
