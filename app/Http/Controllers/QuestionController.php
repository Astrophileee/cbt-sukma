<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            'question_image' => ['nullable', 'image', 'max:2048'],
            'option_images' => ['array'],
            'option_images.*' => ['nullable', 'image', 'max:2048'],
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

        DB::transaction(function () use ($data, $request) {
            $questionImagePath = $request->hasFile('question_image')
                ? $request->file('question_image')->store('questions', 'public')
                : null;

            $question = Question::create([
                'jurusan' => $data['jurusan'],
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'question_image' => $questionImagePath,
                'points' => $this->difficultyPoints[$data['difficulty']] ?? 1,
                'difficulty' => $data['difficulty'],
                'is_published' => $data['is_published'] ?? false,
                'created_by' => Auth::id(),
            ]);

            if ($data['type'] === 'MCQ') {
                $options = $data['options'] ?? [];
                $optionImages = $request->file('option_images', []);
                foreach ($options as $index => $optionText) {
                    if ($optionText === null || $optionText === '') {
                        continue;
                    }

                    $optionImagePath = isset($optionImages[$index])
                        ? $optionImages[$index]->store('question-options', 'public')
                        : null;

                    QuestionOptions::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'option_image' => $optionImagePath,
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
            'question_image' => ['nullable', 'image', 'max:2048'],
            'option_images' => ['array'],
            'option_images.*' => ['nullable', 'image', 'max:2048'],
            'option_ids' => ['array'],
            'option_ids.*' => ['nullable', 'integer'],
            'remove_question_image' => ['nullable', 'boolean'],
            'remove_option_images' => ['array'],
            'remove_option_images.*' => ['nullable', 'boolean'],
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

        DB::transaction(function () use ($data, $question, $request) {
            $questionImagePath = $question->question_image;

            if ($request->hasFile('question_image')) {
                if ($questionImagePath) {
                    Storage::disk('public')->delete($questionImagePath);
                }
                $questionImagePath = $request->file('question_image')->store('questions', 'public');
            } elseif ($request->boolean('remove_question_image')) {
                if ($questionImagePath) {
                    Storage::disk('public')->delete($questionImagePath);
                }
                $questionImagePath = null;
            }

            $question->update([
                'jurusan' => $data['jurusan'],
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'question_image' => $questionImagePath,
                'points' => $this->difficultyPoints[$data['difficulty']] ?? 1,
                'difficulty' => $data['difficulty'],
                'is_published' => $data['is_published'] ?? false,
            ]);

            if ($data['type'] !== 'MCQ') {
                $question->options()->get()->each(function ($option) {
                    if ($option->option_image) {
                        Storage::disk('public')->delete($option->option_image);
                    }
                });
                $question->options()->delete();
                return;
            }

            $options = $data['options'] ?? [];
            $optionImages = $request->file('option_images', []);
            $optionIds = $data['option_ids'] ?? [];
            $removeOptionImages = $request->input('remove_option_images', []);
            $existingOptions = $question->options()->get()->keyBy('id');
            $keptOptionIds = [];

            foreach ($options as $index => $optionText) {
                if ($optionText === null || $optionText === '') {
                    continue;
                }

                $optionId = $optionIds[$index] ?? null;
                $option = $optionId && $existingOptions->has($optionId) ? $existingOptions->get($optionId) : null;
                $optionImagePath = $option->option_image ?? null;

                $shouldRemoveImage = filter_var($removeOptionImages[$index] ?? false, FILTER_VALIDATE_BOOLEAN);
                if ($shouldRemoveImage && $optionImagePath) {
                    Storage::disk('public')->delete($optionImagePath);
                    $optionImagePath = null;
                }

                if (isset($optionImages[$index])) {
                    if ($optionImagePath) {
                        Storage::disk('public')->delete($optionImagePath);
                    }
                    $optionImagePath = $optionImages[$index]->store('question-options', 'public');
                }

                if ($option) {
                    $option->update([
                        'option_text' => $optionText,
                        'option_image' => $optionImagePath,
                        'is_correct' => (int)$data['correct_option'] === $index,
                    ]);
                    $keptOptionIds[] = $option->id;
                } else {
                    $newOption = QuestionOptions::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'option_image' => $optionImagePath,
                        'is_correct' => (int)$data['correct_option'] === $index,
                    ]);
                    $keptOptionIds[] = $newOption->id;
                }
            }

            $optionsToDelete = $question->options()
                ->when(!empty($keptOptionIds), fn($query) => $query->whereNotIn('id', $keptOptionIds))
                ->get();

            $optionsToDelete->each(function ($option) {
                if ($option->option_image) {
                    Storage::disk('public')->delete($option->option_image);
                }
                $option->delete();
            });
        });

        return redirect()->route('questions.index')->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $question->loadMissing('options');

        if ($question->question_image) {
            Storage::disk('public')->delete($question->question_image);
        }

        $question->options->each(function ($option) {
            if ($option->option_image) {
                Storage::disk('public')->delete($option->option_image);
            }
        });

        $question->delete();

        return redirect()->route('questions.index')->with('success', 'Soal berhasil dihapus.');
    }
}
