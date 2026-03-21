<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 32)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'profile_notes')) {
                $table->text('profile_notes')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'browser_notifications_enabled')) {
                $table->boolean('browser_notifications_enabled')->default(false)->after('profile_notes');
            }
            if (! Schema::hasColumn('users', 'current_login_at')) {
                $table->timestamp('current_login_at')->nullable()->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'previous_login_at')) {
                $table->timestamp('previous_login_at')->nullable()->after('current_login_at');
            }
            if (! Schema::hasColumn('users', 'current_login_ip')) {
                $table->string('current_login_ip', 45)->nullable()->after('previous_login_at');
            }
            if (! Schema::hasColumn('users', 'previous_login_ip')) {
                $table->string('previous_login_ip', 45)->nullable()->after('current_login_ip');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            foreach ([
                'previous_login_ip',
                'current_login_ip',
                'previous_login_at',
                'current_login_at',
                'browser_notifications_enabled',
                'profile_notes',
                'phone',
            ] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
