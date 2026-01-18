@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-xl font-bold">{{ $attempt->exam->title }}</h1>
            <p class="text-sm text-gray-600">Access Code: <span class="font-mono">{{ $attempt->exam->access_code }}</span> • Jurusan: {{ $attempt->exam->jurusan }}</p>
            <p class="text-sm text-gray-600">Status: {{ ucfirst($attempt->status) }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-600">Durasi: {{ $attempt->exam->duration_minutes }} menit</p>
            @if($attempt->ends_at)
                <p class="text-sm text-gray-600">Batas: {{ $attempt->ends_at->format('d/m/Y H:i') }}</p>
            @endif
            @if ($attempt->status === 'in_progress' && $attempt->ends_at)
                @php
                    $remainingSeconds = max(0, $attempt->ends_at->diffInSeconds(now(), false));
                @endphp
                <p class="text-sm text-gray-600">Sisa waktu: <span id="exam-timer" data-remaining-seconds="{{ $remainingSeconds }}">--:--</span></p>
            @endif
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        @if ($attempt->status === 'submitted' || $attempt->status === 'graded')
            <div class="mb-4 text-sm text-green-700">Anda sudah mengirim jawaban. Skor sementara: {{ $attempt->score_final ?? $attempt->score_raw }}</div>
        @endif

        <form id="exam-attempt-form" action="{{ route('exams.attempt.submit', $attempt) }}" method="POST" class="space-y-6">
            @csrf
            @foreach ($attempt->attemptQuestions->sortBy('order_no') as $index => $attemptQuestion)
                @php
                    $question = $attemptQuestion->question;
                    $existingAnswer = $attempt->answers->firstWhere('question_id', $question->id);
                @endphp
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm text-gray-500">Soal {{ $index + 1 }} • {{ strtoupper($question->type) }} • Poin {{ $question->points }}</div>
                            <p class="text-gray-800 font-medium mt-1">{{ $question->question_text }}</p>
                        </div>
                    </div>

                    @if ($question->type === 'MCQ')
                        <div class="mt-3 space-y-2">
                            @foreach ($question->options as $option)
                                <label class="flex items-start space-x-2">
                                    <input type="radio"
                                        name="answers[{{ $question->id }}][selected_option_id]"
                                        value="{{ $option->id }}"
                                        class="mt-1"
                                        @if(optional($existingAnswer)->selected_option_id === $option->id) checked @endif
                                        @if($attempt->status !== 'in_progress') disabled @endif>
                                    <span class="text-gray-700 text-sm">{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-3">
                            <textarea
                                name="answers[{{ $question->id }}][answer_text]"
                                rows="4"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                                @if($attempt->status !== 'in_progress') readonly @endif>{{ old('answers.' . $question->id . '.answer_text', optional($existingAnswer)->answer_text) }}</textarea>
                        </div>
                    @endif
                </div>
            @endforeach

            @if ($attempt->status === 'in_progress')
                <div class="flex justify-end">
                    <button id="exam-submit-btn" type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Submit Jawaban</button>
                </div>
            @endif
        </form>
    </div>

    @if (session('success') || session('error'))
        <div id="flash-message"
            data-type="{{ session('success') ? 'success' : 'error' }}"
            data-message="{{ session('success') ?? session('error') }}">
        </div>
    @endif

    @if ($attempt->status === 'in_progress' && $attempt->ends_at)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const timerEl = document.getElementById('exam-timer');
                const form = document.getElementById('exam-attempt-form');

                if (!timerEl || !form) {
                    return;
                }

                let remainingSeconds = parseInt(timerEl.dataset.remainingSeconds, 10);
                if (!Number.isFinite(remainingSeconds)) {
                    return;
                }

                const submitButton = document.getElementById('exam-submit-btn');
                const formatTime = (totalSeconds) => {
                    const hours = Math.floor(totalSeconds / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;
                    const pad = (value) => String(value).padStart(2, '0');

                    if (hours > 0) {
                        return `${hours}:${pad(minutes)}:${pad(seconds)}`;
                    }

                    return `${pad(minutes)}:${pad(seconds)}`;
                };

                const tick = () => {
                    if (remainingSeconds <= 0) {
                        timerEl.textContent = '00:00';
                        if (!form.dataset.autoSubmitted) {
                            form.dataset.autoSubmitted = '1';
                            if (submitButton) {
                                submitButton.disabled = true;
                                submitButton.textContent = 'Mengirim...';
                            }
                            form.submit();
                        }
                        return;
                    }

                    timerEl.textContent = formatTime(remainingSeconds);
                    remainingSeconds -= 1;
                };

                tick();
                setInterval(tick, 1000);
            });
        </script>
    @endif
@endsection
