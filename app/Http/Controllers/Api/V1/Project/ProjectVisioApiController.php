<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\VisioRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectVisioApiController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $rooms = $project->visioRooms()->withCount(['invitations', 'participants'])->get();

        return response()->json([
            'data' => $rooms->map(fn (VisioRoom $room) => [
                'id' => $room->id,
                'slug' => $room->slug,
                'title' => $room->title,
                'status' => $room->status,
                'starts_at' => $room->starts_at?->toIso8601String(),
                'ends_at' => $room->ends_at?->toIso8601String(),
                'invitations_count' => $room->invitations_count,
                'participants_count' => $room->participants_count,
            ]),
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
        ]);

        $room = $project->visioRooms()->create([
            'host_user_id' => $request->user()?->id,
            'title' => $data['title'],
            'starts_at' => $data['starts_at'] ?? null,
            'status' => 'scheduled',
            'meta' => [],
        ]);

        return response()->json(['data' => ['id' => $room->id, 'slug' => $room->slug]], 201);
    }
}
