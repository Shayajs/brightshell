<?php

namespace App\Services\Visio;

use App\Models\User;
use App\Models\VisioInvitation;
use App\Models\VisioParticipant;
use Illuminate\Support\Facades\DB;

class VisioInvitationAcceptor
{
    public function accept(VisioInvitation $invitation, ?User $user, ?string $guestName = null): VisioParticipant
    {
        return DB::transaction(function () use ($invitation, $user, $guestName): VisioParticipant {
            $invitation->refresh();

            if ($invitation->isExpired()) {
                abort(403, 'Cette invitation visio est expirée.');
            }

            if (! $invitation->can_join) {
                abort(403, 'Cette invitation ne permet pas de rejoindre la visio.');
            }

            if ($user !== null && is_string($invitation->email) && $invitation->email !== '') {
                abort_unless(
                    strcasecmp($invitation->email, $user->email) === 0,
                    403,
                    'Cette invitation est liée à une autre adresse e-mail.'
                );
            }

            $participant = VisioParticipant::query()
                ->where('visio_room_id', $invitation->visio_room_id)
                ->when(
                    $user !== null,
                    fn ($q) => $q->where('user_id', $user->id),
                    fn ($q) => $q->whereNull('user_id')->where('guest_name', $this->cleanGuestName($guestName))
                )
                ->latest('id')
                ->first();

            if ($participant === null) {
                $participant = VisioParticipant::create([
                    'visio_room_id' => $invitation->visio_room_id,
                    'user_id' => $user?->id,
                    'guest_name' => $user ? null : $this->cleanGuestName($guestName),
                    'joined_at' => now(),
                    'is_presenter' => (bool) $invitation->can_present,
                ]);
            } else {
                $participant->update([
                    'left_at' => null,
                    'joined_at' => $participant->joined_at ?? now(),
                    'is_presenter' => $participant->is_presenter || (bool) $invitation->can_present,
                ]);
            }

            if ($invitation->accepted_at === null) {
                $invitation->forceFill(['accepted_at' => now()])->save();
            }

            return $participant;
        });
    }

    private function cleanGuestName(?string $value): string
    {
        $name = trim((string) $value);
        if ($name === '') {
            return 'Invité';
        }

        return mb_substr($name, 0, 80);
    }
}
