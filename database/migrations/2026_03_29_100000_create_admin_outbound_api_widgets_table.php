<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_outbound_api_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->string('title', 255);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('http_method', 16)->default('GET');
            $table->string('url', 2048);
            $table->json('query_params')->nullable();
            $table->longText('body')->nullable();
            $table->json('headers')->nullable();
            $table->string('auth_type', 32)->default('none');
            $table->text('auth_secret')->nullable();
            $table->string('auth_header_name', 128)->nullable();
            $table->string('auth_query_param', 128)->nullable();
            $table->string('basic_username', 255)->nullable();
            $table->unsignedSmallInteger('timeout_seconds')->default(20);
            $table->string('fetch_mode', 16)->default('live');
            $table->unsignedSmallInteger('cron_interval_minutes')->nullable();
            $table->timestamp('cached_fetched_at')->nullable();
            $table->unsignedSmallInteger('cached_status_code')->nullable();
            $table->longText('cached_body')->nullable();
            $table->text('last_error')->nullable();
            $table->string('display_mode', 32)->default('raw_json');
            $table->json('display_paths')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_outbound_api_widgets');
    }
};
