<?php

namespace App\Http\Controllers\Courses;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $courses = $user
            ->studentCourses()
            ->with(['quizzes' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $subjectsCount = $user->studentSubjects()->count();

        return view('portals.courses.dashboard', [
            'user' => $user,
            'courses' => $courses,
            'subjectsCount' => $subjectsCount,
        ]);
    }
}
