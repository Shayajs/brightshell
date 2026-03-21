<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class AdminMakeAccountCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'admin:makeaccount
                            {--email= : Email du compte}
                            {--name= : Nom affiche}
                            {--password= : Mot de passe}
                            {--role=admin : Slug role (simple ou liste separee par virgules)}
                            {--force : Met a jour le compte si l\'email existe deja}';

    /**
     * @var string
     */
    protected $description = 'Cree ou met a jour un compte et lui assigne un ou plusieurs roles';

    public function handle(): int
    {
        $email = $this->resolveEmail();
        if ($email === null) {
            return self::FAILURE;
        }

        $name = $this->resolveName();
        if ($name === null) {
            return self::FAILURE;
        }

        $plainPassword = $this->resolvePassword();
        if ($plainPassword === null) {
            return self::FAILURE;
        }

        $roleSlugs = $this->resolveRoleSlugs();
        if ($roleSlugs === null) {
            return self::FAILURE;
        }

        /** @var Collection<int,Role> $roles */
        $roles = Role::query()->whereIn('slug', $roleSlugs)->get();
        if ($roles->count() !== count($roleSlugs)) {
            $found = $roles->pluck('slug')->all();
            $missing = array_values(array_diff($roleSlugs, $found));
            $this->error('Roles introuvables: '.implode(', ', $missing).'. Lance php artisan migrate.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();
        $force = (bool) $this->option('force');

        if ($user !== null && ! $force) {
            $this->error("Un compte existe deja pour {$email}. Utilise --force pour le mettre a jour.");

            return self::FAILURE;
        }

        if ($user === null) {
            $user = new User;
            $user->email = $email;
        }

        $user->name = $name;
        $user->password = $plainPassword;
        $user->is_admin = in_array('admin', $roleSlugs, true);
        $user->save();

        $user->roles()->syncWithoutDetaching($roles->pluck('id')->all());

        $this->components->info('Compte enregistre: '.$email);
        $this->line('Roles: '.implode(', ', $roleSlugs));

        return self::SUCCESS;
    }

    private function resolveEmail(): ?string
    {
        $email = $this->option('email');
        if (is_string($email) && $email !== '') {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Option --email invalide.');

                return null;
            }

            return $email;
        }

        if (! $this->input->isInteractive()) {
            $this->error('Fournis --email en mode non interactif.');

            return null;
        }

        return text(
            label: 'Email du compte',
            required: true,
            validate: fn (string $value) => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : 'Email invalide.',
        );
    }

    private function resolveName(): ?string
    {
        $name = $this->option('name');
        if (is_string($name) && $name !== '') {
            return $name;
        }

        if (! $this->input->isInteractive()) {
            $this->error('Fournis --name en mode non interactif.');

            return null;
        }

        return text(label: 'Nom affiche', default: 'Utilisateur BrightShell', required: true);
    }

    private function resolvePassword(): ?string
    {
        $plain = $this->option('password');
        if (! is_string($plain) || $plain === '') {
            if (! $this->input->isInteractive()) {
                $this->error('Fournis --password en mode non interactif.');

                return null;
            }
            $plain = password(label: 'Mot de passe', required: true);
            $confirm = password(label: 'Confirmation', required: true);
            if ($plain !== $confirm) {
                $this->error('Les mots de passe ne correspondent pas.');

                return null;
            }
        }

        $validator = Validator::make(
            ['password' => $plain],
            ['password' => ['required', Password::defaults()]],
        );
        if ($validator->fails()) {
            $this->error((string) $validator->errors()->first('password'));

            return null;
        }

        return $plain;
    }

    /**
     * @return list<string>|null
     */
    private function resolveRoleSlugs(): ?array
    {
        $raw = $this->option('role');
        if (is_string($raw) && $raw !== '') {
            $slugs = array_values(array_filter(array_map(
                static fn (string $value): string => trim(strtolower($value)),
                explode(',', $raw)
            )));

            if ($slugs === []) {
                $this->error('Option --role vide.');

                return null;
            }

            return array_values(array_unique($slugs));
        }

        if (! $this->input->isInteractive()) {
            $this->error('Fournis --role en mode non interactif.');

            return null;
        }

        $options = Role::query()->orderByDesc('priority')->pluck('slug')->all();
        if ($options === []) {
            $this->error('Aucun role disponible. Lance php artisan migrate.');

            return null;
        }

        /** @var list<string> $selected */
        $selected = multiselect(
            label: 'Roles a attribuer',
            options: $options,
            default: ['admin'],
            required: true,
        );

        return $selected;
    }
}
