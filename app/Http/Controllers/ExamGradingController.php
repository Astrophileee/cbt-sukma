<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use Illuminate\Http\Request;

class ExamGradingController extends Controller
{
    /**
     * Tampilkan halaman penilaian essay.
     */
    public function index()
    {
        $attempts = ExamAttempt::with(['exam', 'user'])
            ->whereIn('status', ['submitted', 'graded'])
            ->whereHas('attemptQuestions.question', function ($query) {
                $query->where('type', 'essay');
            })
            ->latest()
            ->get();

        return view('exams.to_grade', compact('attempts'));
    }

    /**
     * Tampilkan halaman penilaian essay.
     */
    public function show(ExamAttempt $attempt)
    {
        $attempt->load(['exam', 'user', 'answers.question.options', 'attemptQuestions']);

        return view('exams.grade', compact('attempt'));
    }

    /**
     * Simpan hasil penilaian essay.
     */
    public function update(Request $request, ExamAttempt $attempt)
    {
        $attempt->load(['answers.question', 'attemptQuestions.question']);

        $scores = $request->input('scores', []);

        $scoreRaw = 0;
        $maxPoints = $attempt->attemptQuestions->sum(function ($aq) {
            return $aq->question->points ?? 0;
        });

        foreach ($attempt->answers as $answer) {
            $question = $answer->question;
            if ($question->type === 'essay') {
                $inputScore = isset($scores[$answer->id]) ? (int)$scores[$answer->id] : 0;
                $max = $question->points ?? 0;
                $finalScore = max(0, min($inputScore, $max));

                $answer->points_awarded = $finalScore;
                $answer->is_correct = null;
                $answer->save();
            }

            $scoreRaw += $answer->points_awarded;
        }

        $attempt->update([
            'status' => 'graded',
            'score_raw' => $scoreRaw,
            'score_final' => $maxPoints > 0 ? round(($scoreRaw / $maxPoints) * 100, 2) : 0,
        ]);

        return redirect()->route('exams.grade.show', $attempt)->with('success', 'Penilaian berhasil disimpan.');
    }
}
