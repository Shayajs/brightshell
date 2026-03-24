<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

class AdminCompaniesApiController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorizeAdmin();

        $companies = Company::query()->orderBy('name')->get(['id', 'name', 'siret', 'city', 'country']);

        return response()->json(['data' => $companies]);
    }

    private function authorizeAdmin(): void
    {
        $u = auth()->user();
        abort_unless($u && ($u->isAdmin() || $u->hasRole('admin')), 403);
    }
}
