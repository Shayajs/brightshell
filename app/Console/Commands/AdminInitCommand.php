<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class AdminInitCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'admin:init
                            {--email= : Adresse e-mail du compte administrateur}
                            {--name= : Nom affiché}
                            {--password= : Mot de passe (déconseillé : historique shell ; préfère l\'invite)}
                            {--force : Si l\'e-mail existe : promouvoir administrateur et appliquer le mot de passe}';

    /**
     * @var string
     */
    protected $description = 'Crée ou met à jour un compte administrateur (is_admin), sans passer par l’inscription publique';

    public function handle(): int
    {
        $email = $this->option('email');
        if (! is_string($email) || $email === '') {
            if (! $this->input->isInteractive()) {
                $this->error('Fournis --email en mode non-interactif.');

                return self::FAILURE;
            }
            $email = text(
                label: 'E-mail administrateur',
                required: true,
                validate: fn (string $value) => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : 'E-mail invalide.',
            );
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('L’option --email n’est pas une adresse valide.');

            return self::FAILURE;
        }

        $name = $this->option('name');
        if (! is_string($name) || $name === '') {
            if (! $this->input->isInteractive()) {
                $this->error('Fournis --name en mode non-interactif.');

                return self::FAILURE;
            }
            $name = text(
                label: 'Nom affiché',
                default: 'Administrateur',
                required: true,
            );
        }

        $plain = $this->option('password');
        if (! is_string($plain) || $plain === '') {
            if (! $this->input->isInteractive()) {
                $this->error('Fournis --password en mode non-interactif.');

                return self::FAILURE;
            }
            $plain = password(
                label: 'Mot de passe',
                required: true,
            );
            $confirm = password(
                label: 'Confirmation du mot de passe',
                required: true,
            );
            if ($plain !== $confirm) {
                $this->error('Les mots de passe ne correspondent pas.');

                return self::FAILURE;
            }
        }

        $validator = Validator::make(
            ['password' => $plain],
            ['password' => ['required', Password::defaults()]],
        );
        if ($validator->fails()) {
            $this->error((string) $validator->errors()->first('password'));

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user !== null && ! $this->option('force')) {
            $this->error("Un compte existe déjà pour « {$email} ». Lance avec --force pour le promouvoir administrateur et définir ce mot de passe.");

            return self::FAILURE;
        }

        if ($user !== null) {
            $user->name = $name;
            $user->password = $plain;
            $user->is_admin = true;
            $user->save();
            $this->components->info("Compte mis à jour : {$email} (administrateur).");
        } else {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $plain,
                'is_admin' => true,
            ]);
            $this->components->info("Compte administrateur créé : {$email}");
        }

        $adminRole = Role::query()->where('slug', 'admin')->first();
        if ($adminRole !== null) {
            $user->roles()->syncWithoutDetaching([$adminRole->id]);
        } else {
            $this->components->warn('Table des rôles absente : exécute php artisan migrate avant admin:init pour lier le rôle admin.');
        }

        return self::SUCCESS;
    }
}
