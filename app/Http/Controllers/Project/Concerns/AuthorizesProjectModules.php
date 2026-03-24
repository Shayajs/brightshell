<?php

namespace App\Http\Controllers\Project\Concerns;

use App\Models\Project;
use Illuminate\Support\Facades\Gate;

trait AuthorizesProjectModules
{
    protected function authorizeProjectModify(Project $project): void
    {
        Gate::authorize('update', $project);
    }

    protected function authorizeProjectAnnotate(Project $project): void
    {
        if (Gate::denies('annotate', $project) && Gate::denies('update', $project)) {
            abort(403);
        }
    }
}
