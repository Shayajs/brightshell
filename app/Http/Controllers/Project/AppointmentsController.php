<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectAppointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentsController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): View
    {
        $appointments = $project->appointments()->with('creator')->paginate(20);

        return view('portals.project.appointments.index', compact('project', 'appointments'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectModify($project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['created_by'] = $request->user()->id;
        $project->appointments()->create($data);

        return back()->with('success', 'Rendez-vous ajouté.');
    }

    public function update(Request $request, Project $project, ProjectAppointment $appointment): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertAppointmentBelongs($project, $appointment);

        $appointment->update($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]));

        return back()->with('success', 'Rendez-vous mis à jour.');
    }

    public function destroy(Project $project, ProjectAppointment $appointment): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertAppointmentBelongs($project, $appointment);
        $appointment->delete();

        return back()->with('success', 'Rendez-vous supprimé.');
    }

    private function assertAppointmentBelongs(Project $project, ProjectAppointment $appointment): void
    {
        if ($appointment->project_id !== $project->id) {
            abort(404);
        }
    }
}
