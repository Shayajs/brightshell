<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardAnalytics;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AdminDashboardAnalytics $analytics): View
    {
        return view('admin.dashboard', [
            'user' => auth()->user(),
            'dashboard' => $analytics->payload(),
        ]);
    }
}
