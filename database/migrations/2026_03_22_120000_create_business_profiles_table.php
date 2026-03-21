<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('business_profiles')) {
            return;
        }

        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name')->nullable();
            $table->string('trade_name')->nullable();
            /** auto_entrepreneur | ei | other */
            $table->string('legal_status', 32)->default('auto_entrepreneur');
            $table->boolean('vat_registered')->default(false);
            $table->string('vat_number', 32)->nullable();
            $table->string('siret', 14)->nullable();
            $table->string('ape_code', 8)->nullable();
            $table->string('street_line1')->nullable();
            $table->string('street_line2')->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('France');
            $table->string('public_email')->nullable();
            $table->string('public_phone', 32)->nullable();
            $table->string('website_url')->nullable();
            $table->text('activity_description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->boolean('publish_street_on_api')->default(false);
            $table->boolean('publish_siret_on_api')->default(false);
            $table->timestamps();
        });

        DB::table('business_profiles')->insert([
            'legal_name' => null,
            'legal_status' => 'auto_entrepreneur',
            'country' => 'France',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
