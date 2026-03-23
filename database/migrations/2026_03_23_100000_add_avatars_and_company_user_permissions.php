<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path', 512)->nullable()->after('phone');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('logo_path', 512)->nullable()->after('name');
        });

        Schema::table('company_user', function (Blueprint $table) {
            $table->boolean('can_manage_company')->default(false)->after('user_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('company_user', function (Blueprint $table) {
            $table->dropColumn(['can_manage_company', 'created_at', 'updated_at']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_path');
        });
    }
};
