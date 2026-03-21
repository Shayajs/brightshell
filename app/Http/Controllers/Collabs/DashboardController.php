<?php

namespace App\Http\Controllers\Collabs;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('portals.collabs.dashboard', [
            'user' => auth()->user(),
        ]);
    }
}
