@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-xl font-bold">Penilaian Ujian: {{ $attempt->exam->title }}</h1>
            <p class="text-sm text-gray-600">Peserta: {{ $attempt->user->name }} • Access Code: <span class="font-mono">{{ $attempt->exam->access_code }}</span></p>
            <p class="text-sm text-gray-600">Status: {{ ucfirst($attempt->status) }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-600">Skor saat ini: {{ $attempt->score_raw }}</p>
        </div>
    </div>

    <form action="{{ route('exams.grade.update', $attempt) }}" method="POST" class="space-y-4">
        @csrf
        @php
            $orderedAnswers = $attempt->answers->sortBy(function($answer) use ($attempt) {
                return optional($attempt->attemptQuestions->firstWhere('question_id', $answer->question_id))->order_no ?? $answer->id;
            });
        @endphp
        <div class="bg-white shadow rounded-lg p-6 space-y-4">
            @foreach ($orderedAnswers as $index => $answer)
                @php $question = $answer->question; @endphp
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm text-gray-500">Soal {{ $index + 1 }} • {{ strtoupper($question->type) }} • Poin {{ $question->points }}</div>
                            <p class="text-gray-800 font-medium mt-1">{{ $question->question_text }}</p>
                        </div>
                        @if($question->type === 'MCQ')
                            <span class="text-sm {{ $answer->is_correct ? 'text-green-700 font-semibold' : 'text-red-600' }}">
                                {{ $answer->is_correct ? 'Benar' : 'Salah' }} ({{ $answer->points_awarded }} pts)
                            </span>
                        @endif
                    </div>

                    @if ($question->type === 'MCQ')
                        <div class="mt-2 text-sm text-gray-700">
                            Jawaban dipilih:
                            <span class="font-semibold">
                                {{ optional($question->options->firstWhere('id', $answer->selected_option_id))->option_text ?? '-' }}
                            </span>
                        </div>
                    @else
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jawaban Siswa</label>
                            <div class="border border-gray-200 rounded-md p-3 text-sm text-gray-800 bg-gray-50 min-h-[80px]">{{ $answer->answer_text ?? '-' }}</div>
                        </div>
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700">Nilai (0 - {{ $question->points }})</label>
                            <input type="number" name="scores[{{ $answer->id }}]" min="0" max="{{ $question->points }}" value="{{ old('scores.' . $answer->id, $answer->points_awarded) }}" class="w-32 border border-gray-300 rounded-md px-3 py-2 text-sm">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Simpan Penilaian</button>
        </div>
    </form>

    @if (session('success') || session('error'))
        <div id="flash-message"
            data-type="{{ session('success') ? 'success' : 'error' }}"
            data-message="{{ session('success') ?? session('error') }}">
        </div>
    @endif
@endsection
