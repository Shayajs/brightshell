<?php

namespace App\Http\Controllers\Api\V1\Visio;

use App\Http\Controllers\Controller;
use App\Models\VisioInvitation;
use App\Models\VisioParticipant;
use App\Models\VisioRoom;
use App\Services\Visio\LivekitTokenService;
use App\Services\Visio\VisioContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisioRuntimeApiController extends Controller
{
    public function context(Request $request, VisioRoom $room, VisioContextResolver $resolver): JsonResponse
    {
        $this->authorizeRoomAccess($request, $room);

        return response()->json([
            'data' => $resolver->roomContext($room),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function token(
        Request $request,
        VisioRoom $room,
        LivekitTokenService $livekitTokenService
    ): JsonResponse {
        [$invitation, $participant] = $this->authorizeRoomAccess($request, $room);

        $canPublish = true;
        if ($invitation !== null) {
            $canPublish = (bool) $invitation->can_present;
        }

        $token = $livekitTokenService->issueRoomToken(
            $room,
            $request->user(),
            $participant,
            ['canPublish' => $canPublish]
        );

        return response()->json([
            'data' => [
                'token' => $token,
                'ws_url' => (string) config('brightshell.livekit.ws_url', ''),
                'room' => $room->slug,
            ],
        ]);
    }

    public function heartbeat(Request $request, VisioRoom $room): JsonResponse
    {
        [, $participant] = $this->authorizeRoomAccess($request, $room);

        if ($participant !== null) {
            $participant->forceFill([
                'joined_at' => $participant->joined_at ?? now(),
                'left_at' => null,
            ])->save();
        }

        return response()->json(['ok' => true]);
    }

    public function updateContext(
        Request $request,
        VisioRoom $room,
        VisioContextResolver $resolver
    ): JsonResponse {
        $user = $request->user();
        abort_unless($user !== null, 401);
        abort_unless($room->project !== null, 422, 'Salle non liée à un projet.');
        $this->authorize('update', $room->project);

        $data = $request->validate([
            'student_subject_file_id' => ['nullable', 'integer', 'exists:student_subject_files,id'],
        ]);

        if (! empty($data['student_subject_file_id'])) {
            $resolver->updateSharedDocument($room, (int) $data['student_subject_file_id']);
        }

        return response()->json(['message' => 'Contexte visio mis à jour.']);
    }

    /**
     * @return array{0: VisioInvitation|null, 1: VisioParticipant|null}
     */
    private function authorizeRoomAccess(Request $request, VisioRoom $room): array
    {
        $user = $request->user();
        $token = (string) ($request->input('token') ?: $request->session()->get('visio_invitation_token_'.$room->id, ''));

        if ($user !== null) {
            $invitation = null;
            if ($token !== '') {
                $invitation = $room->invitations()->where('token', $token)->first();
                if ($invitation !== null) {
                    abort_if($invitation->isExpired() || ! $invitation->can_join, 403);
                    if (is_string($invitation->email) && $invitation->email !== '') {
                        abort_unless(strcasecmp($invitation->email, $user->email) === 0, 403);
                    }
                }
            }

            if ($room->project !== null && $invitation === null) {
                $this->authorize('view', $room->project);
            }

            $participant = $room->participants()
                ->where('user_id', $user->id)
                ->latest('id')
                ->first();

            return [$invitation, $participant];
        }

        abort_unless($token !== '', 403);

        $invitation = $room->invitations()->where('token', $token)->first();
        abort_unless($invitation !== null && ! $invitation->isExpired() && $invitation->can_join, 403);

        $participant = $room->participants()
            ->whereNull('user_id')
            ->latest('id')
            ->first();

        return [$invitation, $participant];
    }
}
