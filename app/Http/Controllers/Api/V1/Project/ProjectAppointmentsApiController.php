<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectAppointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectAppointmentsApiController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $appointments = $project->appointments()->with('creator:id,first_name,last_name')->paginate(20);

        return response()->json($appointments);
    }

    public function store(Request $request, Project $project): JsonResponse
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
        $appointment = $project->appointments()->create($data);
        $appointment->load('creator:id,first_name,last_name');

        return response()->json(['data' => $this->appointmentPayload($appointment)], 201);
    }

    public function update(Request $request, Project $project, ProjectAppointment $appointment): JsonResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertBelongs($project, $appointment);

        $appointment->update($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]));
        $appointment->load('creator:id,first_name,last_name');

        return response()->json(['data' => $this->appointmentPayload($appointment)]);
    }

    public function destroy(Project $project, ProjectAppointment $appointment): JsonResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertBelongs($project, $appointment);
        $appointment->delete();

        return response()->json(['message' => 'Rendez-vous supprimé.']);
    }

    private function assertBelongs(Project $project, ProjectAppointment $appointment): void
    {
        abort_unless($appointment->project_id === $project->id, 404);
    }

    /** @return array<string, mixed> */
    private function appointmentPayload(ProjectAppointment $a): array
    {
        return [
            'id' => $a->id,
            'title' => $a->title,
            'starts_at' => $a->starts_at?->toIso8601String(),
            'ends_at' => $a->ends_at?->toIso8601String(),
            'location' => $a->location,
            'notes' => $a->notes,
            'creator' => $a->creator ? ['id' => $a->creator->id, 'name' => $a->creator->name] : null,
        ];
    }
}
