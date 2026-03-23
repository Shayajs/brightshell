<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->default('')->after('id');
            $table->string('last_name')->default('')->after('first_name');
        });

        foreach (DB::table('users')->select('id', 'name')->get() as $row) {
            [$first, $last] = self::splitLegacyName((string) $row->name);
            DB::table('users')->where('id', $row->id)->update([
                'first_name' => $first,
                'last_name' => $last,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->default('')->after('last_name');
        });

        foreach (DB::table('users')->select('id', 'first_name', 'last_name')->get() as $row) {
            $first = trim((string) ($row->first_name ?? ''));
            $last = trim((string) ($row->last_name ?? ''));
            $name = trim($first.' '.$last);
            DB::table('users')->where('id', $row->id)->update(['name' => $name]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function splitLegacyName(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['', ''];
        }
        $p = strpos($name, ' ');
        if ($p === false) {
            return [$name, ''];
        }

        return [substr($name, 0, $p), trim(substr($name, $p + 1))];
    }
};
