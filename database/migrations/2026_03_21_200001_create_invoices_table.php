<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();         // ex. BS-2026-001
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount_ht', 10, 2)->default(0);
            $table->decimal('tva_rate', 5, 2)->nullable();  // null = micro-entreprise (sans TVA)
            $table->string('status')->default('draft');     // draft|sent|paid|cancelled
            $table->string('label')->nullable();            // objet de la facture
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
