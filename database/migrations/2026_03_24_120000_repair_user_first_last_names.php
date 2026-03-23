<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Réparation après split prénom/nom :
 * - Si la colonne legacy `name` existe encore : on remplit first/last puis on supprime `name`.
 * - Si prénom et nom sont tous deux vides : on dérive une valeur depuis la partie locale de l’e-mail.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'name')) {
            foreach (DB::table('users')->select('id', 'name', 'first_name', 'last_name')->get() as $row) {
                $hasFirst = trim((string) ($row->first_name ?? '')) !== '';
                $hasLast = trim((string) ($row->last_name ?? '')) !== '';
                if (! $hasFirst && ! $hasLast) {
                    [$f, $l] = User::splitFullName((string) ($row->name ?? ''));
                    DB::table('users')->where('id', $row->id)->update([
                        'first_name' => $f,
                        'last_name' => $l,
                    ]);
                }
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }

        foreach (DB::table('users')->select('id', 'email', 'first_name', 'last_name')->get() as $row) {
            if (trim((string) ($row->first_name ?? '')) !== '' || trim((string) ($row->last_name ?? '')) !== '') {
                continue;
            }

            $local = Str::before((string) ($row->email ?? ''), '@');
            $local = $local === '' ? 'Compte' : str_replace(['.', '_', '-'], ' ', $local);
            $local = trim(preg_replace('/\s+/u', ' ', $local) ?? $local);
            if ($local === '') {
                $local = 'Compte';
            }

            $p = strpos($local, ' ');
            if ($p === false) {
                DB::table('users')->where('id', $row->id)->update([
                    'first_name' => $local,
                    'last_name' => '',
                ]);
            } else {
                DB::table('users')->where('id', $row->id)->update([
                    'first_name' => substr($local, 0, $p),
                    'last_name' => trim(substr($local, $p + 1)),
                ]);
            }
        }
    }

    public function down(): void
    {
        // irréversible sans recréer `name`
    }
};
