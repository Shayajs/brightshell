<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table): void {
            // ─── Snapshot probe HTTP du site web ─────────────────────────────
            $table->boolean('website_probed')->default(false)->after('logo_url');
            $table->boolean('website_alive')->nullable()->after('website_probed');
            $table->boolean('website_https')->nullable()->after('website_alive');
            $table->boolean('website_responsive')->nullable()->after('website_https');
            $table->string('website_platform', 32)->nullable()->after('website_responsive');
            $table->string('website_platform_version', 16)->nullable()->after('website_platform');
            $table->smallInteger('website_copyright_year')->nullable()->after('website_platform_version');
            $table->smallInteger('website_status_code')->nullable()->after('website_copyright_year');
            $table->timestamp('website_probed_at')->nullable()->after('website_status_code');
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table): void {
            $table->dropColumn([
                'website_probed',
                'website_alive',
                'website_https',
                'website_responsive',
                'website_platform',
                'website_platform_version',
                'website_copyright_year',
                'website_status_code',
                'website_probed_at',
            ]);
        });
    }
};
