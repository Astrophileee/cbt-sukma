<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    private array $majors = ['Taiwan', 'Polandia', 'Hongkong', 'Jepang', 'Korea', 'Turkey', 'Malaysia', 'Singapura'];
    private array $difficultyPoints = ['easy' => 1, 'medium' => 2, 'hard' => 3];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $questions = Question::with('options')->latest()->get();
        $majors = $this->majors;

        return view('questions.index', compact('questions', 'majors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jurusan' => ['required', Rule::in($this->majors)],
            'type' => ['required', Rule::in(['MCQ', 'essay'])],
            'question_text' => ['required', 'string'],
            'difficulty' => ['required', Rule::in(array_keys($this->difficultyPoints))],
            'is_published' => ['nullable', 'boolean'],
            'options' => ['array'],
            'options.*' => ['nullable', 'string'],
            'correct_option' => ['nullable', 'integer'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->type === 'MCQ') {
                $options = array_filter($request->input('options', []), fn($opt) => $opt !== null && $opt !== '');
                if (count($options) < 2 || count($options) > 4) {
                    $validator->errors()->add('options', 'Opsi harus antara 2 sampai 4 pilihan.');
                }
                $correct = $request->input('correct_option');
                if ($correct === null || !isset($request->input('options', [])[$correct]) || $request->input('options', [])[$correct] === '') {
                    $validator->errors()->add('correct_option', 'Pilih jawaban yang benar.');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->route('questions.index')->withErrors($validator)->withInput()->with('openModal', 'add');
        }

        $data = $validator->validated();

        DB::transaction(function () use ($data) {
            $question = Question::create([
                'jurusan' => $data['jurusan'],
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'points' => $this->difficultyPoints[$data['difficulty']] ?? 1,
                'difficulty' => $data['difficulty'],
                'is_published' => $data['is_published'] ?? false,
                'created_by' => Auth::id(),
            ]);

            if ($data['type'] === 'MCQ') {
                $options = $data['options'] ?? [];
                foreach ($options as $index => $optionText) {
                    if ($optionText === null || $optionText === '') {
                        continue;
                    }
                    QuestionOptions::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'is_correct' => (int)$data['correct_option'] === $index,
                    ]);
                }
            }
        });

        return redirect()->route('questions.index')->with('success', 'Soal berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question)
    {
        $validator = Validator::make($request->all(), [
            'jurusan' => ['required', Rule::in($this->majors)],
            'type' => ['required', Rule::in(['MCQ', 'essay'])],
            'question_text' => ['required', 'string'],
            'difficulty' => ['required', Rule::in(array_keys($this->difficultyPoints))],
            'is_published' => ['nullable', 'boolean'],
            'options' => ['array'],
            'options.*' => ['nullable', 'string'],
            'correct_option' => ['nullable', 'integer'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->type === 'MCQ') {
                $options = array_filter($request->input('options', []), fn($opt) => $opt !== null && $opt !== '');
                if (count($options) < 2 || count($options) > 4) {
                    $validator->errors()->add('options', 'Opsi harus antara 2 sampai 4 pilihan.');
                }
                $correct = $request->input('correct_option');
                if ($correct === null || !isset($request->input('options', [])[$correct]) || $request->input('options', [])[$correct] === '') {
                    $validator->errors()->add('correct_option', 'Pilih jawaban yang benar.');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->route('questions.index')
                ->withErrors($validator)
                ->withInput()
                ->with('editQuestion', $question->load('options'));
        }

        $data = $validator->validated();

        DB::transaction(function () use ($data, $question) {
            $question->update([
                'jurusan' => $data['jurusan'],
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'points' => $this->difficultyPoints[$data['difficulty']] ?? 1,
                'difficulty' => $data['difficulty'],
                'is_published' => $data['is_published'] ?? false,
            ]);

            $question->options()->delete();

            if ($data['type'] === 'MCQ') {
                $options = $data['options'] ?? [];
                foreach ($options as $index => $optionText) {
                    if ($optionText === null || $optionText === '') {
                        continue;
                    }
                    QuestionOptions::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'is_correct' => (int)$data['correct_option'] === $index,
                    ]);
                }
            }
        });

        return redirect()->route('questions.index')->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $question->delete();

        return redirect()->route('questions.index')->with('success', 'Soal berhasil dihapus.');
    }
}
