<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\BrightshieldUserConsent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Passport\Client;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

class ConnectedAppsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $consents = BrightshieldUserConsent::forUser($user);

        $apps = $consents->map(function (BrightshieldUserConsent $consent) {
            $client = Client::query()->find($consent->client_id);

            return [
                'consent' => $consent,
                'client' => $client,
                'label' => config('brightshield.client_labels.'.strtolower((string) ($client?->name ?? '')), [
                    'title' => $client?->name ?? 'Application',
                    'description' => null,
                ]),
            ];
        });

        return view('portals.settings.connected-apps', [
            'apps' => $apps,
        ]);
    }

    public function destroy(Request $request, string $clientId): RedirectResponse
    {
        $user = $request->user();

        BrightshieldUserConsent::revoke($user, $clientId);

        $tokenIds = Token::query()
            ->where('user_id', $user->id)
            ->where('client_id', $clientId)
            ->pluck('id');

        Token::query()
            ->where('user_id', $user->id)
            ->where('client_id', $clientId)
            ->update(['revoked' => true]);

        RefreshToken::query()
            ->whereIn('access_token_id', $tokenIds)
            ->update(['revoked' => true]);

        return redirect()
            ->route('portals.settings.connected-apps.index')
            ->with('success', 'Accès révoqué pour cette application.');
    }
}
