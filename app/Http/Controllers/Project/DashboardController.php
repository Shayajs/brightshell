<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $projects = Project::query()
            ->forUser($user)
            ->with('company')
            ->orderedForDisplay()
            ->get();

        return view('portals.project.dashboard', [
            'user' => $user,
            'projects' => $projects,
        ]);
    }
}
