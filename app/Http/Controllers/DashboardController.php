<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->roles->pluck('name')->first();

        $adminStats = null;
        $teacherStats = null;
        $studentStats = null;
        $upcomingExams = collect();
        $pastExams = collect();

        if ($user->hasRole('admin')) {
            $adminStats = [
                'users' => User::count(),
                'students' => Student::count(),
                'exams' => Exam::count(),
                'questions' => Question::count(),
                'attempts' => ExamAttempt::count(),
            ];
        }

        if ($user->hasRole('guru')) {
            $teacherStats = [
                'questions' => Question::where('created_by', $user->id)->count(),
                'exams' => Exam::where('created_by', $user->id)->count(),
                'exam_attempts' => ExamAttempt::whereHas('exam', fn($q) => $q->where('created_by', $user->id))->count(),
            ];
        }

        if ($user->hasRole('siswa')) {
            $student = $user->student()->first();
            $major = $student?->major;

            $attemptQuery = ExamAttempt::where('user_id', $user->id);
            $studentStats = [
                'total_attempts' => (clone $attemptQuery)->count(),
                'completed_attempts' => (clone $attemptQuery)->whereIn('status', ['submitted', 'graded'])->count(),
            ];

            if ($major) {
                $upcomingExams = Exam::where('jurusan', $major)
                    ->where('status', 'published')
                    ->where(function ($q) {
                        $q->whereNull('start_at')->orWhere('start_at', '>=', now());
                    })
                    ->orderBy('start_at')
                    ->take(4)
                    ->get();

                $pastExams = Exam::where('jurusan', $major)
                    ->whereNotNull('start_at')
                    ->where('start_at', '<', now())
                    ->orderByDesc('start_at')
                    ->take(4)
                    ->get();
            }
        }

        return view('dashboard', compact(
            'role',
            'adminStats',
            'teacherStats',
            'studentStats',
            'upcomingExams',
            'pastExams'
        ));
    }
}
