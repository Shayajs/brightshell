<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('portals.settings.dashboard', [
            'user' => auth()->user(),
        ]);
    }
}
