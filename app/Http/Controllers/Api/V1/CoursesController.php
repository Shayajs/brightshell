<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\StudentCourseResource;
use App\Models\StudentCourse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('student')) {
            return response()->json(['message' => 'Rôle élève requis pour accéder aux cours.'], 403);
        }

        $courses = $user
            ->studentCourses()
            ->with(['quizzes' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return StudentCourseResource::collection($courses)->response();
    }

    public function show(Request $request, StudentCourse $studentCourse): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('student')) {
            return response()->json(['message' => 'Rôle élève requis pour accéder aux cours.'], 403);
        }

        abort_unless($studentCourse->user_id === $user->id, 404);

        $studentCourse->load(['quizzes' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order')->orderBy('id')]);

        return (new StudentCourseResource($studentCourse))->response();
    }
}
