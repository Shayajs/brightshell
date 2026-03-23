<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('doc_nodes')->cascadeOnDelete();
            $table->string('slug', 128);
            $table->string('title', 255);
            $table->boolean('is_folder')->default(false);
            $table->longText('body')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['parent_id', 'slug']);
        });

        Schema::create('doc_node_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_node_id')->constrained('doc_nodes')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['doc_node_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_node_role');
        Schema::dropIfExists('doc_nodes');
    }
};
