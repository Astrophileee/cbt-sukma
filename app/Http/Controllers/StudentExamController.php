<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\AttemptAnswer;
use App\Models\AttemptQuestion;
use App\Models\QuestionOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
     * Unduh laporan PDF nilai attempt milik user.
     */
    public function downloadReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = !empty($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : null;

        $attempts = ExamAttempt::with('exam')
            ->where('user_id', Auth::id())
            ->whereNotNull('submitted_at')
            ->when($startDate, fn($query) => $query->where('submitted_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('submitted_at', '<=', $endDate))
            ->orderByDesc('submitted_at')
            ->get();

        $pdf = Pdf::loadView('exams.my_attempts_report', [
            'attempts' => $attempts,
            'user' => $request->user(),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $fileName = 'laporan-nilai-' . now()->format('YmdHis') . '.pdf';

        return $pdf->download($fileName);
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

        $now = now();
        if ($exam->start_at && $now->lt($exam->start_at)) {
            return back()->withErrors(['access_code' => 'Ujian belum dimulai. Silakan coba lagi saat waktu ujian dimulai.'])->withInput();
        }

        if ($exam->end_at && $now->gt($exam->end_at)) {
            return back()->withErrors(['access_code' => 'Ujian sudah berakhir.'])->withInput();
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
            $endsAt = $now->copy()->addMinutes($exam->duration_minutes);
            if ($exam->end_at && $exam->end_at->lt($endsAt)) {
                $endsAt = $exam->end_at;
            }

            $attempt = DB::transaction(function () use ($exam, $userId, $now, $endsAt) {
                $attempt = ExamAttempt::create([
                    'exam_id' => $exam->id,
                    'user_id' => $userId,
                    'started_at' => $now,
                    'ends_at' => $endsAt,
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

        $attempt->loadMissing('exam');
        if ($this->shouldAutoSubmit($attempt)) {
            $this->finalizeAttempt($attempt, [], $this->resolveAutoSubmittedAt($attempt));

            return redirect()
                ->route('exams.attempt.show', $attempt)
                ->with('error', 'Waktu ujian sudah habis. Jawaban disubmit otomatis.');
        }

        $attempt->load(['attemptQuestions.question.options', 'answers']);

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

        $submittedAt = $attempt->ends_at && $attempt->ends_at->isPast()
            ? $attempt->ends_at
            : now();

        $this->finalizeAttempt($attempt, $request->input('answers', []), $submittedAt);

        return redirect()->route('exams.attempt.show', $attempt)->with('success', 'Jawaban berhasil disubmit.');
    }

    private function shouldAutoSubmit(ExamAttempt $attempt): bool
    {
        if ($attempt->status !== 'in_progress') {
            return false;
        }

        $attemptEnded = $attempt->ends_at && $attempt->ends_at->isPast();
        $examEnded = $attempt->exam?->end_at && $attempt->exam->end_at->isPast();

        return $attemptEnded || $examEnded;
    }

    private function resolveAutoSubmittedAt(ExamAttempt $attempt): Carbon
    {
        $submittedAt = $attempt->ends_at;
        $examEndAt = $attempt->exam?->end_at;

        if ($examEndAt && (!$submittedAt || $examEndAt->lt($submittedAt))) {
            $submittedAt = $examEndAt;
        }

        return $submittedAt ?? now();
    }

    private function finalizeAttempt(ExamAttempt $attempt, array $answersInput, ?Carbon $submittedAt = null): void
    {
        $attempt->load(['exam', 'attemptQuestions.question.options']);

        $hasEssay = $attempt->attemptQuestions->contains(function ($attemptQuestion) {
            $type = $attemptQuestion->question->type ?? '';
            return strtolower($type) === 'essay';
        });
        $status = $hasEssay ? 'submitted' : 'graded';

        $maxPoints = $attempt->attemptQuestions->sum(function ($attemptQuestion) {
            return $attemptQuestion->question->points ?? 0;
        });

        DB::transaction(function () use ($attempt, $answersInput, $submittedAt, $status) {
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
                'submitted_at' => $submittedAt ?? now(),
                'status' => $status,
                'score_raw' => $scoreRaw,
                'score_final' => 0, // updated after transaction
            ]);
        });

        $attempt->refresh();
        $scoreFinal = $maxPoints > 0 ? round(($attempt->score_raw / $maxPoints) * 100, 2) : 0;
        $attempt->update(['score_final' => $scoreFinal]);
    }
}
