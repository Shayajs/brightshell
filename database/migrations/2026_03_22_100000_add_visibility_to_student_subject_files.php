<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_subject_files')) {
            return;
        }

        Schema::table('student_subject_files', function (Blueprint $table): void {
            if (! Schema::hasColumn('student_subject_files', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('sort_order');
            }
            if (! Schema::hasColumn('student_subject_files', 'is_hidden_from_student')) {
                $table->boolean('is_hidden_from_student')->default(false)->after('is_locked');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_subject_files')) {
            return;
        }

        Schema::table('student_subject_files', function (Blueprint $table): void {
            if (Schema::hasColumn('student_subject_files', 'is_hidden_from_student')) {
                $table->dropColumn('is_hidden_from_student');
            }
            if (Schema::hasColumn('student_subject_files', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
        });
    }
};
