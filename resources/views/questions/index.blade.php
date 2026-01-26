@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Bank Soal</h1>
        <button onclick="document.getElementById('modal-tambah-question').classList.remove('hidden')" class="bg-black text-white px-4 py-2 rounded-md shadow hover:bg-gray-800">
            Tambah Soal
        </button>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table id="questionsTable" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jurusan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipe</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Soal</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Opsi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Bobot</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Published</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($questions as $question)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $question->jurusan }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ strtoupper($question->type) }}</td>
                        <td class="px-6 py-4 text-gray-700">
                            <div class="space-y-2">
                                <div>{{ \Illuminate\Support\Str::limit($question->question_text, 80) }}</div>
                                @if ($question->question_image)
                                    <img src="{{ Storage::url($question->question_image) }}" alt="Gambar soal {{ $question->id }}" class="h-16 rounded border object-contain">
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-700">
                            @if ($question->type === 'MCQ')
                            <ul class="list-disc list-inside text-sm space-y-2">
                                @foreach ($question->options as $option)
                                <li class="{{ $option->is_correct ? 'font-semibold text-green-700' : '' }}">
                                    <div>{{ $option->option_text }}</div>
                                    @if ($option->option_image)
                                        <img src="{{ Storage::url($option->option_image) }}" alt="Gambar opsi" class="h-12 mt-1 rounded border object-contain">
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                            @else
                            <span class="text-gray-500 text-sm">Essay</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $question->points }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                            @if($question->is_published)
                                <span class="text-green-700 font-semibold">Ya</span>
                            @else
                                <span class="text-gray-500">Tidak</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button
                                    type="button"
                                    class="text-blue-600 hover:text-blue-900 border border-blue-600 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    onclick='openEditModal(@json($question->load("options")))'>
                                    Edit
                                </button>
                                <form id="deleteForm{{ $question->id }}" action="{{ route('questions.destroy', $question) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="button"
                                        onclick="confirmDelete('{{ $question->id }}')"
                                        class="text-red-600 hover:text-red-900 border border-red-600 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-200">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Tambah -->
    <div id="modal-tambah-question" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 hidden">
        <div class="min-h-screen flex items-center justify-center py-6 px-4">
            <div class="bg-white w-full max-w-4xl mx-auto rounded-lg shadow-lg p-6 relative">
                <button onclick="document.getElementById('modal-tambah-question').classList.add('hidden')" class="absolute top-4 right-4 text-xl font-bold text-gray-600 hover:text-gray-800">&times;</button>

                <h2 class="text-lg font-semibold mb-4">Tambah Soal</h2>

                <form action="{{ route('questions.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jurusan *</label>
                            <select name="jurusan" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white">
                                <option value="" disabled {{ old('jurusan') ? '' : 'selected' }}>Pilih jurusan</option>
                                @foreach ($majors as $jurusan)
                                    <option value="{{ $jurusan }}" {{ old('jurusan') === $jurusan ? 'selected' : '' }}>{{ $jurusan }}</option>
                                @endforeach
                            </select>
                            @error('jurusan')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipe *</label>
                            <select name="type" id="addType" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white" onchange="toggleOptionsBlock('add', this.value)">
                                <option value="" disabled {{ old('type') ? '' : 'selected' }}>Pilih tipe</option>
                                <option value="MCQ" {{ old('type') === 'MCQ' ? 'selected' : '' }}>MCQ</option>
                                {{-- <option value="essay" {{ old('type') === 'essay' ? 'selected' : '' }}>Essay</option> --}}
                            </select>
                            @error('type')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Soal *</label>
                        <textarea name="question_text" rows="3" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">{{ old('question_text') }}</textarea>
                        @error('question_text')
                            <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Foto Soal (opsional)</label>
                        <input type="file" name="question_image" accept="image/*" class="mt-1 text-sm">
                        <p class="text-xs text-gray-500 mt-1">Akan ditampilkan di soal jika diisi.</p>
                        @error('question_image')
                            <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Poin *</label>
                                <input type="number" name="points" id="addPoints" value="{{ old('points', 1) }}" min="1" required readonly class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-gray-50">
                                @error('points')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                            <label class="block text-sm font-medium text-gray-700">Kesulitan</label>
                            <select name="difficulty" id="addDifficulty" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white" onchange="updatePoints('add', this.value)">
                                <option value="" disabled {{ old('difficulty') ? '' : 'selected' }}>Pilih tingkat</option>
                                @foreach (['easy', 'medium', 'hard'] as $level)
                                    <option value="{{ $level }}" {{ old('difficulty') === $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
                                @endforeach
                            </select>
                            @error('difficulty')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="flex items-center mt-6 space-x-2">
                            <input type="checkbox" name="is_published" value="1" id="addPublished" {{ old('is_published') ? 'checked' : '' }}>
                            <label for="addPublished" class="text-sm text-gray-700">Published</label>
                        </div>
                    </div>

                    <div id="addOptionsBlock" class="{{ old('type') === 'MCQ' ? '' : 'hidden' }}">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-gray-700">Opsi (pilih jawaban benar)</p>
                            <span class="text-xs text-gray-500">Minimal 2, maksimal 4</span>
                        </div>
                        <div class="space-y-3">
                            @for ($i = 0; $i < 4; $i++)
                                <div class="flex items-start space-x-3">
                                    <input type="radio" name="correct_option" value="{{ $i }}" class="mt-2" {{ old('correct_option') == $i ? 'checked' : '' }}>
                                    <div class="flex-1 space-y-2">
                                        <input type="text" name="options[{{ $i }}]" value="{{ old('options.' . $i) }}" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="Opsi {{ $i + 1 }}">
                                        <div class="flex items-center gap-2">
                                            <input type="file" name="option_images[{{ $i }}]" accept="image/*" class="text-xs">
                                            <span class="text-xs text-gray-500">Foto opsi (opsional)</span>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                        @error('options')
                            <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                        @enderror
                        @error('correct_option')
                            <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                        @enderror
                        @error('option_images.*')
                            <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="resetAddForm(); document.getElementById('modal-tambah-question').classList.add('hidden')" class="px-4 py-2 rounded-md border text-sm">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="modal-edit-question" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 hidden">
        <div class="min-h-screen flex items-center justify-center py-6 px-4">
            <div class="bg-white w-full max-w-4xl mx-auto rounded-lg shadow-lg p-6 relative">
                <button onclick="document.getElementById('modal-edit-question').classList.add('hidden')" class="absolute top-4 right-4 text-xl font-bold text-gray-600 hover:text-gray-800">&times;</button>
                <h2 class="text-lg font-semibold mb-4">Edit Soal</h2>

                <form id="editQuestionForm" method="POST" action="{{ route('questions.update', ['question' => '__ID__']) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jurusan *</label>
                                <select name="jurusan" id="editJurusan" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white">
                                    <option value="" disabled selected>Pilih jurusan</option>
                                    @foreach ($majors as $jurusan)
                                        <option value="{{ $jurusan }}">{{ $jurusan }}</option>
                                    @endforeach
                                </select>
                                @error('jurusan')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipe *</label>
                                <select name="type" id="editType" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white" onchange="toggleOptionsBlock('edit', this.value)">
                                    <option value="" disabled selected>Pilih tipe</option>
                                    <option value="MCQ">MCQ</option>
                                    {{-- <option value="essay">Essay</option> --}}
                                </select>
                                @error('type')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Soal *</label>
                            <textarea name="question_text" id="editQuestionText" rows="3" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">{{ old('question_text') }}</textarea>
                            @error('question_text')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Foto Soal (opsional)</label>
                            <div class="space-y-2 mt-1">
                                <input type="file" name="question_image" id="editQuestionImage" accept="image/*" class="text-sm">
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="remove_question_image" id="editQuestionImageRemove" value="1">
                                    <span>Hapus foto saat ini</span>
                                </label>
                                <img id="editQuestionImagePreview" src="" alt="Gambar soal" class="h-24 rounded border object-contain hidden">
                            </div>
                            @error('question_image')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Poin *</label>
                                <input type="number" name="points" id="editPoints" min="1" required readonly class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-gray-50">
                                @error('points')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kesulitan</label>
                                <select name="difficulty" id="editDifficulty" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white" onchange="updatePoints('edit', this.value)">
                                    <option value="" disabled selected>Pilih tingkat</option>
                                    @foreach (['easy', 'medium', 'hard'] as $level)
                                        <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                                    @endforeach
                                </select>
                                @error('difficulty')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="flex items-center mt-6 space-x-2">
                                <input type="checkbox" name="is_published" value="1" id="editPublished">
                                <label for="editPublished" class="text-sm text-gray-700">Published</label>
                            </div>
                        </div>

                        <div id="editOptionsBlock" class="hidden">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-gray-700">Opsi (pilih jawaban benar)</p>
                                <span class="text-xs text-gray-500">Minimal 2, maksimal 4</span>
                            </div>
                            <div class="space-y-3">
                                @for ($i = 0; $i < 4; $i++)
                                    <div class="flex items-start space-x-3">
                                        <input type="radio" name="correct_option" id="editCorrect{{ $i }}" value="{{ $i }}" class="mt-2">
                                        <div class="flex-1 space-y-2">
                                            <input type="hidden" name="option_ids[{{ $i }}]" id="editOptionId{{ $i }}">
                                            <input type="text" name="options[{{ $i }}]" id="editOption{{ $i }}" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="Opsi {{ $i + 1 }}">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <input type="file" name="option_images[{{ $i }}]" id="editOptionImage{{ $i }}" accept="image/*" class="text-xs">
                                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                                    <input type="checkbox" name="remove_option_images[{{ $i }}]" id="editRemoveOptionImage{{ $i }}" value="1">
                                                    <span>Hapus foto opsi</span>
                                                </label>
                                            </div>
                                            <img id="editOptionPreview{{ $i }}" src="" alt="Gambar opsi {{ $i + 1 }}" class="h-20 rounded border object-contain hidden">
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            @error('options')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                            @error('correct_option')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                            @error('option_images.*')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" onclick="resetEditForm(); document.getElementById('modal-edit-question').classList.add('hidden')" class="px-4 py-2 rounded-md border text-sm">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @vite(['resources/js/app.js'])
    <script>
        const storageBaseUrl = @json(rtrim(Storage::url(''), '/'));

        function toggleOptionsBlock(prefix, type) {
            const block = document.getElementById(`${prefix}OptionsBlock`);
            if (type === 'MCQ') {
                block.classList.remove('hidden');
            } else {
                block.classList.add('hidden');
            }
        }

        function getPointsFromDifficulty(difficulty) {
            const map = { easy: 1, medium: 2, hard: 3 };
            return map[difficulty?.toLowerCase()] ?? 1;
        }

        function updatePoints(prefix, difficulty) {
            const input = document.getElementById(`${prefix}Points`);
            input.value = getPointsFromDifficulty(difficulty);
        }

        function buildStorageUrl(path = '') {
            if (!path) return '';
            return `${storageBaseUrl}/${path.replace(/^\/+/, '')}`;
        }

        function setImagePreview(elementId, path) {
            const el = document.getElementById(elementId);
            if (!el) return;
            const url = buildStorageUrl(path);
            el.src = url;
            el.classList.toggle('hidden', !url);
        }

        function openEditModal(question, overrides = {}) {
            const modal = document.getElementById('modal-edit-question');
            modal.classList.remove('hidden');

            const form = document.getElementById('editQuestionForm');
            const baseAction = form.dataset.action ?? form.getAttribute('action');
            form.dataset.action = baseAction;
            form.action = baseAction.replace('__ID__', question.id);

            setSelectValueCaseInsensitive(document.getElementById('editJurusan'), overrides.jurusan ?? question.jurusan ?? '');
            document.getElementById('editType').value = overrides.type ?? question.type ?? '';
            toggleOptionsBlock('edit', document.getElementById('editType').value);

            document.getElementById('editQuestionText').value = overrides.question_text ?? question.question_text ?? '';
            setSelectValueCaseInsensitive(document.getElementById('editDifficulty'), overrides.difficulty ?? question.difficulty ?? '');
            updatePoints('edit', document.getElementById('editDifficulty').value);
            document.getElementById('editPublished').checked = Boolean(overrides.is_published ?? question.is_published);

            setImagePreview('editQuestionImagePreview', overrides.question_image ?? question.question_image ?? '');
            const removeQuestionImage = document.getElementById('editQuestionImageRemove');
            if (removeQuestionImage) {
                removeQuestionImage.checked = Boolean(overrides.remove_question_image);
            }
            const questionImageInput = document.getElementById('editQuestionImage');
            if (questionImageInput) {
                questionImageInput.value = '';
            }

            for (let i = 0; i < 4; i++) {
                const optionValue = overrides.options?.[i] ?? (question.options?.[i]?.option_text ?? '');
                document.getElementById(`editOption${i}`).value = optionValue;
                const radio = document.getElementById(`editCorrect${i}`);
                const isCorrect = overrides.correct_option !== undefined
                    ? Number(overrides.correct_option) === i
                    : (question.options?.[i]?.is_correct ?? false);
                radio.checked = isCorrect;

                const optionIdInput = document.getElementById(`editOptionId${i}`);
                if (optionIdInput) {
                    optionIdInput.value = overrides.option_ids?.[i] ?? (question.options?.[i]?.id ?? '');
                }

                setImagePreview(`editOptionPreview${i}`, question.options?.[i]?.option_image ?? '');
                const removeOptionCheckbox = document.getElementById(`editRemoveOptionImage${i}`);
                if (removeOptionCheckbox) {
                    removeOptionCheckbox.checked = Boolean(overrides.remove_option_images?.[i]);
                }
                const optionImageInput = document.getElementById(`editOptionImage${i}`);
                if (optionImageInput) {
                    optionImageInput.value = '';
                }
            }
        }

        function setSelectValueCaseInsensitive(selectEl, value) {
            if (!value) {
                selectEl.value = '';
                return;
            }
            const target = value.toString().toLowerCase();
            let matched = false;
            Array.from(selectEl.options).forEach(opt => {
                if (opt.value.toString().toLowerCase() === target) {
                    opt.selected = true;
                    matched = true;
                }
            });
            if (!matched) {
                selectEl.value = '';
            }
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm' + id).submit();
                }
            });
        }

        function resetAddForm() {
            const form = document.querySelector('#modal-tambah-question form');
            form.reset();
            toggleOptionsBlock('add', 'MCQ');
            updatePoints('add', 'easy');
        }

        function resetEditForm() {
            const form = document.getElementById('editQuestionForm');
            form.reset();
            toggleOptionsBlock('edit', 'MCQ');
            updatePoints('edit', 'easy');
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if($errors->any() && session('openModal') === 'add')
                document.getElementById('modal-tambah-question').classList.remove('hidden');
                toggleOptionsBlock('add', document.getElementById('addType').value || 'MCQ');
                updatePoints('add', document.getElementById('addDifficulty').value || 'easy');
            @endif
            updatePoints('add', document.getElementById('addDifficulty').value || 'easy');
        });

        @if(session('editQuestion'))
            window.onload = function() {
                const q = @json(session('editQuestion'));
                openEditModal(q, {
                    jurusan: @json(old('jurusan')),
                    type: @json(old('type')),
                    question_text: @json(old('question_text')),
                    points: @json(old('points')),
                    difficulty: @json(old('difficulty')),
                    is_published: @json(old('is_published')),
                    options: @json(old('options')),
                    correct_option: @json(old('correct_option')),
                    option_ids: @json(old('option_ids')),
                    remove_question_image: @json(old('remove_question_image')),
                    remove_option_images: @json(old('remove_option_images')),
                });
            }
        @endif
    </script>

    @if (session('success') || session('error'))
        <div id="flash-message"
            data-type="{{ session('success') ? 'success' : 'error' }}"
            data-message="{{ session('success') ?? session('error') }}">
        </div>
    @endif
@endsection
