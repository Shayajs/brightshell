<?php

namespace App\Services\Visio;

use App\Models\User;
use App\Models\VisioParticipant;
use App\Models\VisioRoom;
use RuntimeException;

class LivekitTokenService
{
    /**
     * Vérifie que LiveKit est activé et configuré.
     */
    private function ensureLiveKitConfigured(): void
    {
        $enabled = filter_var(config('brightshell.livekit.enabled', false), FILTER_VALIDATE_BOOLEAN);
        
        if (! $enabled) {
            throw new RuntimeException(
                'La visioconférence LiveKit est désactivée. Configurez LIVEKIT_ENABLED=true dans .env.'
            );
        }
        
        $apiKey = (string) config('brightshell.livekit.api_key', '');
        $apiSecret = (string) config('brightshell.livekit.api_secret', '');
        $wsUrl = (string) config('brightshell.livekit.ws_url', '');
        
        if ($apiKey === '' || $apiSecret === '') {
            throw new RuntimeException(
                'LiveKit activé mais non configuré. Définissez LIVEKIT_API_KEY et LIVEKIT_API_SECRET dans .env.'
            );
        }
        
        if ($wsUrl === '') {
            throw new RuntimeException(
                'LiveKit activé mais ws_url manquant. Définissez LIVEKIT_WS_URL dans .env.'
            );
        }
    }

    /**
     * Génère un JWT LiveKit "Access Token" sans dépendance externe.
     *
     * @param  array<string, mixed>  $grants
     */
    public function issueRoomToken(
        VisioRoom $room,
        ?User $user,
        ?VisioParticipant $participant,
        array $grants = []
    ): string {
        $this->ensureLiveKitConfigured();
        
        $apiKey = (string) config('brightshell.livekit.api_key', '');
        $apiSecret = (string) config('brightshell.livekit.api_secret', '');

        $identity = $user?->id !== null
            ? 'user-'.$user->id
            : 'guest-'.($participant?->id ?? bin2hex(random_bytes(4)));

        $name = $user?->name ?: ($participant?->guest_name ?: 'Invité');
        $now = time();

        $payload = [
            'iss' => $apiKey,
            'sub' => $identity,
            'name' => $name,
            'nbf' => $now - 5,
            'iat' => $now,
            'exp' => $now + 7200,
            'video' => array_merge([
                'room' => $room->slug,
                'roomJoin' => true,
                'canPublish' => true,
                'canSubscribe' => true,
                'canPublishData' => true,
            ], $grants),
        ];

        return $this->encodeJwt($payload, $apiSecret);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encodeJwt(array $payload, string $secret): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $segments = [
            $this->base64UrlEncode((string) json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->base64UrlEncode((string) json_encode($payload, JSON_UNESCAPED_SLASHES)),
        ];

        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $secret, true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
