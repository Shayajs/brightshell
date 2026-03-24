<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotesController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): View
    {
        $notes = $project->notes()->with('user')->paginate(30);

        return view('portals.project.notes.index', compact('project', 'notes'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectAnnotate($project);

        $data = $request->validate(['body' => ['required', 'string', 'max:20000']]);
        $project->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Note ajoutée.');
    }

    public function destroy(Request $request, Project $project, ProjectNote $note): RedirectResponse
    {
        if ($note->project_id !== $project->id) {
            abort(404);
        }

        $user = $request->user();
        if ($note->user_id === $user->id) {
            $this->authorizeProjectAnnotate($project);
        } else {
            $this->authorizeProjectModify($project);
        }

        $note->delete();

        return back()->with('success', 'Note supprimée.');
    }
}
