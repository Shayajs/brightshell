<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Support\PortalNavigation;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        return view('portals.home.dashboard', [
            'user' => $user,
            'portalTiles' => PortalNavigation::accessiblePortals($user),
        ]);
    }
}
