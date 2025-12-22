<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    private array $majors = ['Taiwan', 'Polandia', 'Hongkong', 'Jepang', 'Korea', 'Turkey', 'Malaysia', 'Singapura'];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exams = Exam::with(['questions.options'])->latest()->get();
        $questions = Question::where('is_published', true)->with('options')->get();
        $majors = $this->majors;

        return view('exams.index', compact('exams', 'questions', 'majors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'jurusan' => ['required', Rule::in($this->majors)],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'selection_mode' => ['required', Rule::in(['manual', 'automatic'])],
            'total_questions' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'question_ids' => ['array'],
            'question_ids.*' => ['exists:questions,id'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $selectionMode = $request->input('selection_mode');
            $total = (int)$request->input('total_questions');
            $questionIds = array_filter($request->input('question_ids', []));
            $jurusan = $request->input('jurusan');

            if ($selectionMode === 'manual') {
                if (count($questionIds) !== $total) {
                    $validator->errors()->add('question_ids', 'Jumlah soal yang dipilih harus sama dengan total soal.');
                }
                if (!empty($questionIds)) {
                    $mismatchCount = Question::whereIn('id', $questionIds)
                        ->where('jurusan', '!=', $jurusan)
                        ->count();
                    if ($mismatchCount > 0) {
                        $validator->errors()->add('question_ids', 'Semua soal manual harus sesuai jurusan.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->route('exams.index')
                ->withErrors($validator)
                ->withInput()
                ->with('openModal', 'add');
        }

        $data = $validator->validated();

        if ($data['selection_mode'] === 'automatic') {
            $availableCount = Question::where('jurusan', $data['jurusan'])
                ->where('is_published', true)
                ->count();
            if ($availableCount < $data['total_questions']) {
                return redirect()->route('exams.index')
                    ->withErrors(['question_ids' => 'Jumlah soal tersedia tidak mencukupi untuk mode otomatis.'])
                    ->withInput()
                    ->with('openModal', 'add');
            }
        }

        DB::transaction(function () use ($data) {
            $exam = Exam::create([
                'created_by' => Auth::id(),
                'title' => $data['title'],
                'jurusan' => $data['jurusan'],
                'access_code' => $this->generateAccessCode(),
                'duration_minutes' => $data['duration_minutes'],
                'start_at' => $data['start_at'] ?? null,
                'end_at' => $data['end_at'] ?? null,
                'selection_mode' => $data['selection_mode'],
                'total_questions' => $data['total_questions'],
                'status' => $data['status'] ?? 'draft',
            ]);

            $questionIds = [];
            if ($data['selection_mode'] === 'manual') {
                $questionIds = array_values(array_filter($data['question_ids'] ?? []));
            } else {
                $questionIds = Question::where('jurusan', $data['jurusan'])
                    ->where('is_published', true)
                    ->inRandomOrder()
                    ->limit($data['total_questions'])
                    ->pluck('id')
                    ->toArray();
            }

            foreach ($questionIds as $index => $questionId) {
                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question_id' => $questionId,
                    'order_no' => $index + 1,
                ]);
            }
        });

        return redirect()->route('exams.index')->with('success', 'Exam berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exam $exam)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'jurusan' => ['required', Rule::in($this->majors)],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'selection_mode' => ['required', Rule::in(['manual', 'automatic'])],
            'total_questions' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'question_ids' => ['array'],
            'question_ids.*' => ['exists:questions,id'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $selectionMode = $request->input('selection_mode');
            $total = (int)$request->input('total_questions');
            $questionIds = array_filter($request->input('question_ids', []));
            $jurusan = $request->input('jurusan');

            if ($selectionMode === 'manual') {
                if (count($questionIds) !== $total) {
                    $validator->errors()->add('question_ids', 'Jumlah soal yang dipilih harus sama dengan total soal.');
                }
                if (!empty($questionIds)) {
                    $mismatchCount = Question::whereIn('id', $questionIds)
                        ->where('jurusan', '!=', $jurusan)
                        ->count();
                    if ($mismatchCount > 0) {
                        $validator->errors()->add('question_ids', 'Semua soal manual harus sesuai jurusan.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->route('exams.index')
                ->withErrors($validator)
                ->withInput()
                ->with('editExam', $exam->load('questions.options'));
        }

        $data = $validator->validated();

        if ($data['selection_mode'] === 'automatic') {
            $availableCount = Question::where('jurusan', $data['jurusan'])
                ->where('is_published', true)
                ->count();
            if ($availableCount < $data['total_questions']) {
                return redirect()->route('exams.index')
                    ->withErrors(['question_ids' => 'Jumlah soal tersedia tidak mencukupi untuk mode otomatis.'])
                    ->withInput()
                    ->with('editExam', $exam->load('questions.options'));
            }
        }

        DB::transaction(function () use ($data, $exam) {
            $exam->update([
                'title' => $data['title'],
                'jurusan' => $data['jurusan'],
                'duration_minutes' => $data['duration_minutes'],
                'start_at' => $data['start_at'] ?? null,
                'end_at' => $data['end_at'] ?? null,
                'selection_mode' => $data['selection_mode'],
                'total_questions' => $data['total_questions'],
                'status' => $data['status'] ?? $exam->status,
            ]);

            $exam->examQuestions()->delete();

            $questionIds = [];
            if ($data['selection_mode'] === 'manual') {
                $questionIds = array_values(array_filter($data['question_ids'] ?? []));
            } else {
                $questionIds = Question::where('jurusan', $data['jurusan'])
                    ->where('is_published', true)
                    ->inRandomOrder()
                    ->limit($data['total_questions'])
                    ->pluck('id')
                    ->toArray();
            }

            foreach ($questionIds as $index => $questionId) {
                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question_id' => $questionId,
                    'order_no' => $index + 1,
                ]);
            }
        });

        return redirect()->route('exams.index')->with('success', 'Exam berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exam $exam)
    {
        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'Exam berhasil dihapus.');
    }

    private function generateAccessCode(): string
    {
        do {
            $code = Str::upper(Str::random(12));
        } while (Exam::where('access_code', $code)->exists());

        return $code;
    }
}
