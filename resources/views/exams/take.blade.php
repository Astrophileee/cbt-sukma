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
                    $remainingSeconds = max(0, now()->diffInSeconds($attempt->ends_at, false));
                @endphp
                <p class="text-sm text-gray-600">Sisa waktu: <span id="exam-timer" data-remaining-seconds="{{ $remainingSeconds }}">--:--</span></p>
            @endif
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        @if ($attempt->status === 'submitted' || $attempt->status === 'graded')
            <div class="mb-4 text-sm text-green-700">Anda sudah mengirim jawaban. Skor sementara: {{ $attempt->score_final ?? $attempt->score_raw }}</div>
        @endif

        @php
            $sortedQuestions = $attempt->attemptQuestions->sortBy('order_no');
            $totalQuestions = $sortedQuestions->count();
        @endphp
        <form id="exam-attempt-form" action="{{ route('exams.attempt.submit', $attempt) }}" method="POST" class="space-y-6">
            @csrf
            <div class="border border-yellow-100 rounded-lg p-4 bg-yellow-50/40">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="text-sm text-gray-700">
                        Soal <span id="current-question-index" class="font-semibold text-gray-900">1</span> dari {{ $totalQuestions }}
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="question-prev" type="button" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-gray-200 text-gray-600 hover:bg-white transition">Sebelumnya</button>
                        <button id="question-next" type="button" class="px-3 py-1.5 text-xs font-semibold rounded-md bg-yellow-500 text-gray-900 hover:bg-yellow-600 transition">Berikutnya</button>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 gap-2">
                    @foreach ($sortedQuestions as $index => $attemptQuestion)
                        <button type="button"
                            class="question-number-btn h-9 rounded-md border border-gray-200 text-sm font-semibold text-gray-600 hover:border-yellow-400 hover:text-yellow-700 transition"
                            data-question-nav="{{ $index }}">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>
            </div>
            @foreach ($sortedQuestions as $index => $attemptQuestion)
                @php
                    $question = $attemptQuestion->question;
                    $existingAnswer = $attempt->answers->firstWhere('question_id', $question->id);
                @endphp
                <section class="question-section border border-gray-200 rounded-md p-4 {{ $index === 0 ? '' : 'hidden' }}" data-question-index="{{ $index }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm text-gray-500">Soal {{ $index + 1 }} • {{ strtoupper($question->type) }} • Poin {{ $question->points }}</div>
                            <p class="text-gray-800 font-medium mt-1">{{ $question->question_text }}</p>
                            @if ($question->question_image)
                                <img src="{{ Storage::url($question->question_image) }}" alt="Gambar soal" data-full-url="{{ Storage::url($question->question_image) }}" class="exam-image mt-2 max-h-52 rounded border object-contain cursor-zoom-in">
                            @endif
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
                                    <div class="text-gray-700 text-sm space-y-1">
                                        <div>{{ $option->option_text }}</div>
                                        @if ($option->option_image)
                                            <img src="{{ Storage::url($option->option_image) }}" alt="Gambar opsi" data-full-url="{{ Storage::url($option->option_image) }}" class="exam-image h-28 rounded border object-contain cursor-zoom-in">
                                        @endif
                                    </div>
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
                </section>
            @endforeach

            @if ($attempt->status === 'in_progress')
                <div class="flex justify-end">
                    <button id="exam-submit-btn" type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Submit Jawaban</button>
                </div>
            @endif
        </form>
    </div>

    <div id="image-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
        <div class="relative max-w-5xl w-full">
            <button id="image-modal-close" type="button" class="absolute -top-3 -right-3 h-9 w-9 rounded-full bg-white text-gray-700 shadow hover:bg-gray-100">✕</button>
            <div class="bg-white rounded-lg p-3">
                <img id="image-modal-img" src="" alt="Preview gambar" class="max-h-[80vh] w-full object-contain rounded">
            </div>
        </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sections = Array.from(document.querySelectorAll('.question-section'));
            if (!sections.length) {
                return;
            }

            const currentLabel = document.getElementById('current-question-index');
            const navButtons = Array.from(document.querySelectorAll('[data-question-nav]'));
            const prevBtn = document.getElementById('question-prev');
            const nextBtn = document.getElementById('question-next');
            const form = document.getElementById('exam-attempt-form');
            let currentIndex = 0;

            const setButtonState = (button, enabled) => {
                if (!button) return;
                button.disabled = !enabled;
                button.classList.toggle('opacity-50', !enabled);
                button.classList.toggle('cursor-not-allowed', !enabled);
            };

            const updateView = (index) => {
                currentIndex = Math.max(0, Math.min(index, sections.length - 1));
                sections.forEach((section, idx) => {
                    section.classList.toggle('hidden', idx !== currentIndex);
                });
                navButtons.forEach((button) => {
                    const idx = parseInt(button.dataset.questionNav, 10);
                    const isActive = idx === currentIndex;
                    button.classList.toggle('bg-yellow-500', isActive);
                    button.classList.toggle('text-gray-900', isActive);
                    button.classList.toggle('border-yellow-500', isActive);
                    button.classList.toggle('bg-white', !isActive);
                    button.classList.toggle('text-gray-600', !isActive);
                });
                if (currentLabel) {
                    currentLabel.textContent = String(currentIndex + 1);
                }
                setButtonState(prevBtn, currentIndex > 0);
                setButtonState(nextBtn, currentIndex < sections.length - 1);
                if (form) {
                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            };

            navButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const idx = parseInt(button.dataset.questionNav, 10);
                    if (Number.isFinite(idx)) {
                        updateView(idx);
                    }
                });
            });

            if (prevBtn) {
                prevBtn.addEventListener('click', () => updateView(currentIndex - 1));
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', () => updateView(currentIndex + 1));
            }

            updateView(0);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('image-modal');
            const modalImg = document.getElementById('image-modal-img');
            const closeBtn = document.getElementById('image-modal-close');

            if (!modal || !modalImg) {
                return;
            }

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modalImg.src = '';
            };

            document.addEventListener('click', function (event) {
                const target = event.target.closest('.exam-image');
                if (!target) {
                    return;
                }
                const fullUrl = target.dataset.fullUrl || target.getAttribute('src');
                if (!fullUrl) {
                    return;
                }
                modalImg.src = fullUrl;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });
        });
    </script>
@endsection
