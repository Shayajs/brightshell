<?php

namespace App\Http\Controllers\Collabs;

use App\Http\Controllers\Controller;
use App\Models\CollaboratorTeam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamMessagesController extends Controller
{
    private const INITIAL_LIMIT = 80;

    public function index(Request $request, CollaboratorTeam $collab_team): View
    {
        $this->authorize('participateInMessages', $collab_team);

        $messages = $collab_team->messages()
            ->with('user')
            ->orderByDesc('id')
            ->limit(self::INITIAL_LIMIT)
            ->get()
            ->sortBy('id')
            ->values();

        return view('portals.collabs.teams.messages', [
            'team' => $collab_team,
            'messages' => $messages,
        ]);
    }

    public function poll(Request $request, CollaboratorTeam $collab_team): JsonResponse
    {
        $this->authorize('participateInMessages', $collab_team);

        $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $afterId = (int) $request->input('after_id', 0);

        $rows = $collab_team->messages()
            ->with('user')
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit(200)
            ->get();

        return response()->json([
            'messages' => $rows->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'created_at' => $m->created_at?->toIso8601String(),
                'user' => $m->user ? [
                    'id' => $m->user->id,
                    'name' => $m->user->name,
                ] : null,
            ]),
        ]);
    }

    public function store(Request $request, CollaboratorTeam $collab_team): JsonResponse
    {
        $this->authorize('participateInMessages', $collab_team);

        $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $message = $collab_team->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $request->string('body')->trim(),
        ]);

        $message->load('user');

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at?->toIso8601String(),
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                ],
            ],
        ], 201);
    }
}
