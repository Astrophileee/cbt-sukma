<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\QuestionOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentExamController extends Controller
{
    /**
     * Form akses kode ujian.
     */
    public function showAccessForm()
    {
        return view('exams.join');
    }

    /**
     * Daftar attempt milik user.
     */
    public function myAttempts()
    {
        $attempts = ExamAttempt::with(['exam'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('exams.my_attempts', compact('attempts'));
    }

    /**
     * Mulai ujian berdasarkan access code.
     */
    public function start(Request $request)
    {
        $request->validate([
            'access_code' => ['required', 'string'],
        ]);

        $exam = Exam::where('access_code', $request->input('access_code'))
            ->where('status', 'published')
            ->first();

        if (!$exam) {
            return back()->withErrors(['access_code' => 'Kode ujian tidak ditemukan atau belum dipublikasikan.'])->withInput();
        }

        if ($exam->questions()->count() < $exam->total_questions) {
            return back()->withErrors(['access_code' => 'Soal pada ujian ini belum lengkap.'])->withInput();
        }

        $userId = Auth::id();

        // Jika sudah pernah submit, tidak boleh ulang
        $completedAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $userId)
            ->whereIn('status', ['submitted', 'graded'])
            ->first();
        if ($completedAttempt) {
            return back()->withErrors(['access_code' => 'Anda sudah menyelesaikan ujian ini.'])->withInput();
        }

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $userId)
            ->whereIn('status', ['not_started', 'in_progress'])
            ->first();

        if (!$attempt) {
            $attempt = DB::transaction(function () use ($exam, $userId) {
                $attempt = ExamAttempt::create([
                    'exam_id' => $exam->id,
                    'user_id' => $userId,
                    'started_at' => now(),
                    'ends_at' => now()->addMinutes($exam->duration_minutes),
                    'status' => 'in_progress',
                ]);

                $questionIds = $exam->questions()->pluck('questions.id')->shuffle()->take($exam->total_questions);
                foreach ($questionIds as $index => $questionId) {
                    AttemptQuestion::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                        'order_no' => $index + 1,
                    ]);
                }

                return $attempt;
            });
        }

        return redirect()->route('exams.attempt.show', $attempt);
    }

    /**
     * Tampilkan halaman ujian untuk siswa.
     */
    public function showAttempt(ExamAttempt $attempt)
    {
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }

        $attempt->load(['exam', 'attemptQuestions.question.options', 'answers']);

        return view('exams.take', compact('attempt'));
    }

    /**
     * Submit jawaban ujian.
     */
    public function submitAttempt(Request $request, ExamAttempt $attempt)
    {
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }

        if (in_array($attempt->status, ['submitted', 'graded'])) {
            return redirect()->route('exams.attempt.show', $attempt)->withErrors(['error' => 'Ujian sudah disubmit.']);
        }

        $attempt->load(['exam', 'attemptQuestions.question.options']);
        $answersInput = $request->input('answers', []);
        $maxPoints = $attempt->attemptQuestions->sum(function ($aq) {
            return $aq->question->points ?? 0;
        });

        DB::transaction(function () use ($attempt, $answersInput) {
            $attempt->answers()->delete();

            $scoreRaw = 0;

            foreach ($attempt->attemptQuestions as $attemptQuestion) {
                $question = $attemptQuestion->question;
                $input = $answersInput[$question->id] ?? [];

                if ($question->type === 'MCQ') {
                    $selectedId = $input['selected_option_id'] ?? null;
                    $selectedOption = null;
                    if ($selectedId) {
                        $selectedOption = QuestionOptions::where('question_id', $question->id)
                            ->where('id', $selectedId)
                            ->first();
                    }

                    $isCorrect = $selectedOption ? (bool)$selectedOption->is_correct : false;
                    $points = $isCorrect ? $question->points : 0;
                    $scoreRaw += $points;

                    AttemptAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'selected_option_id' => $selectedOption?->id,
                        'answer_text' => null,
                        'is_correct' => $isCorrect,
                        'points_awarded' => $points,
                    ]);
                } else { // essay
                    $text = $input['answer_text'] ?? null;

                    AttemptAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'selected_option_id' => null,
                        'answer_text' => $text,
                        'is_correct' => null,
                        'points_awarded' => 0,
                    ]);
                }
            }

            $attempt->update([
                'submitted_at' => now(),
                'status' => 'submitted',
                'score_raw' => $scoreRaw,
                'score_final' => 0, // updated after transaction
            ]);
        });

        $attempt->refresh();
        $scoreRaw = $attempt->score_raw;
        $scoreFinal = $maxPoints > 0 ? round(($scoreRaw / $maxPoints) * 100, 2) : 0;
        $attempt->update(['score_final' => $scoreFinal]);

        return redirect()->route('exams.attempt.show', $attempt)->with('success', 'Jawaban berhasil disubmit.');
    }
}
