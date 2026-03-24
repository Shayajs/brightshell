<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Confirmation de deconnexion</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-xl items-center px-4">
        <section class="w-full rounded-2xl border border-zinc-800 bg-zinc-900/70 p-6 shadow-2xl shadow-black/40 sm:p-8">
            <h1 class="text-xl font-semibold text-zinc-100 sm:text-2xl">
                Souhaitez vous reellement vous deconnecter ?
            </h1>

            <p class="mt-3 text-sm text-zinc-400">
                La session a expire pendant la deconnexion (erreur 419). Vous pouvez confirmer l'action.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <form method="POST" action="{{ route('logout') }}" class="sm:flex-1">
                    @csrf
                    <button
                        type="submit"
                        class="w-full rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-200 transition hover:bg-red-500/20"
                    >
                        Oui, se deconnecter
                    </button>
                </form>

                <button
                    type="button"
                    onclick="history.back()"
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800/70 px-4 py-2.5 text-sm font-semibold text-zinc-200 transition hover:bg-zinc-700/70 sm:flex-1"
                >
                    Non, revenir en arriere
                </button>
            </div>
        </section>
    </main>
</body>
</html>
