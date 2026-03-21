<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_subjects')) {
            Schema::create('student_subjects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['user_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('student_subject_folders')) {
            Schema::create('student_subject_folders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_subject_id')->constrained()->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('student_subject_folders')->cascadeOnDelete();
                $table->string('name');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['student_subject_id', 'parent_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('student_subject_files')) {
            Schema::create('student_subject_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_subject_folder_id')->constrained()->cascadeOnDelete();
                $table->string('original_name');
                $table->string('stored_path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subject_files');
        Schema::dropIfExists('student_subject_folders');
        Schema::dropIfExists('student_subjects');
    }
};
