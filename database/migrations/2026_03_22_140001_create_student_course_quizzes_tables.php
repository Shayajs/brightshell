<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_course_quizzes')) {
            Schema::create('student_course_quizzes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_course_id')->constrained('student_courses')->cascadeOnDelete();
                $table->string('title');
                $table->text('instructions')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_published')->default(true);
                $table->timestamps();

                $table->index(['student_course_id', 'sort_order'], 'scq_course_sort_idx');
            });
        }

        if (! Schema::hasTable('student_course_quiz_questions')) {
            Schema::create('student_course_quiz_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_course_quiz_id')->constrained('student_course_quizzes')->cascadeOnDelete();
                $table->text('body');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['student_course_quiz_id', 'sort_order'], 'scqq_quiz_sort_idx');
            });
        }

        if (! Schema::hasTable('student_course_quiz_answers')) {
            Schema::create('student_course_quiz_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_course_quiz_question_id');
                $table->string('body');
                $table->boolean('is_correct')->default(false);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('student_course_quiz_question_id', 'scqa_question_fk')
                    ->references('id')->on('student_course_quiz_questions')->cascadeOnDelete();

                $table->index(['student_course_quiz_question_id', 'sort_order'], 'scqa_q_sort_idx');
            });
        }

        if (! Schema::hasTable('student_quiz_attempts')) {
            Schema::create('student_quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_course_quiz_id')->constrained('student_course_quizzes')->cascadeOnDelete();
                $table->unsignedTinyInteger('score_percent');
                $table->json('responses')->nullable();
                $table->timestamp('completed_at')->useCurrent();
                $table->timestamps();

                $table->index(['user_id', 'student_course_quiz_id'], 'sqa_user_quiz_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_quiz_attempts');
        Schema::dropIfExists('student_course_quiz_answers');
        Schema::dropIfExists('student_course_quiz_questions');
        Schema::dropIfExists('student_course_quizzes');
    }
};
