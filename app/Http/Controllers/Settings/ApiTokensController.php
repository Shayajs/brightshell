<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokensController extends Controller
{
    public function index(Request $request): View
    {
        $tokens = $request->user()->tokens()->orderByDesc('id')->get();

        return view('portals.settings.api-tokens', [
            'tokens' => $tokens,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $token = $request->user()->createToken($validated['name']);

        return redirect()
            ->route('portals.settings.api.index')
            ->with('success', 'Jeton créé. Copiez-le maintenant : il ne sera plus affiché en clair.')
            ->with('new_api_token_plain', $token->plainTextToken);
    }

    public function destroy(Request $request, int $token): RedirectResponse
    {
        $pat = PersonalAccessToken::query()
            ->where('id', $token)
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $request->user()->id)
            ->firstOrFail();

        $pat->delete();

        return redirect()
            ->route('portals.settings.api.index')
            ->with('success', 'Jeton révoqué.');
    }
}
