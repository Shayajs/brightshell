<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class RegisterOAuthClientCommand extends Command
{
    protected $signature = 'brightshield:register-client
                            {key : Clé config brightshield.clients (ex. futurmeal)}
                            {--redirect=* : URI(s) de redirection OAuth}
                            {--reset-secret : Régénère le secret client (affiche le plain text une fois)}';

    protected $description = 'Enregistre ou met à jour un client OAuth BrightShield';

    public function handle(ClientRepository $clients): int
    {
        $key = strtolower((string) $this->argument('key'));
        $definition = config('brightshield.clients.'.$key);

        if (! is_array($definition)) {
            $this->error("Client « {$key} » introuvable dans config/brightshield.php.");

            return self::FAILURE;
        }

        $redirects = $this->option('redirect');
        if ($redirects === [] || $redirects === null) {
            $redirects = $definition['redirect_uris'] ?? [];
        }

        $redirects = array_values(array_filter(array_map('trim', $redirects)));
        if ($redirects === []) {
            $this->error('Au moins une redirect URI est requise (--redirect ou config).');

            return self::FAILURE;
        }

        $name = (string) ($definition['name'] ?? $key);
        $existing = Client::query()->where('name', $name)->first();

        if ($existing !== null) {
            $existing->forceFill([
                'redirect_uris' => $redirects,
                'revoked' => false,
            ])->save();

            $this->info("Client « {$name} » mis à jour.");
            $this->line('Client ID : '.$existing->getKey());

            if ($this->option('reset-secret')) {
                $clients->regenerateSecret($existing);
                $existing->refresh();
                $this->warn('Nouveau secret (à copier dans BRIGHTSHIELD_CLIENT_SECRET) :');
                $this->line($existing->plainSecret);
            } else {
                $this->comment('Secret inchangé. Utilisez --reset-secret pour en générer un nouveau.');
            }

            return self::SUCCESS;
        }

        $client = $clients->createAuthorizationCodeGrantClient(
            $name,
            $redirects,
            true,
            null,
            false,
        );

        $this->info("Client « {$name} » créé.");
        $this->line('Client ID : '.$client->getKey());
        $this->line('Client secret : '.$client->plainSecret);
        $this->newLine();
        $this->comment('À coller dans Futurmeal .env :');
        $this->line('BRIGHTSHIELD_CLIENT_ID='.$client->getKey());
        $this->line('BRIGHTSHIELD_CLIENT_SECRET='.$client->plainSecret);

        return self::SUCCESS;
    }
}
