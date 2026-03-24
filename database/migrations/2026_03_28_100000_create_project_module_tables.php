<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('project_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('project_kanban_boards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('project_kanban_columns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('board_id')->constrained('project_kanban_boards')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_kanban_cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('column_id')->constrained('project_kanban_columns')->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('project_documents')->nullOnDelete();
            $table->string('title');
            $table->string('disk')->default('public');
            $table->string('path')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_spec_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('title');
            $table->longText('body')->nullable();
            $table->string('status', 32)->default('draft');
            $table->timestamps();
        });

        Schema::create('project_contracts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('reference');
            $table->string('status', 32)->default('draft');
            $table->date('effective_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->foreignId('signed_document_id')->nullable()->constrained('project_documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('project_price_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price_ht', 14, 4);
            $table->decimal('vat_rate', 6, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('status', 32)->default('open');
            $table->foreignId('support_ticket_id')->nullable()->constrained('support_tickets')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_requests');
        Schema::dropIfExists('project_price_items');
        Schema::dropIfExists('project_contracts');
        Schema::dropIfExists('project_spec_sections');
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('project_kanban_cards');
        Schema::dropIfExists('project_kanban_columns');
        Schema::dropIfExists('project_kanban_boards');
        Schema::dropIfExists('project_notes');
        Schema::dropIfExists('project_appointments');
    }
};
