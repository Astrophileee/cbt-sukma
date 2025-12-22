@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Kelola Exam</h1>
        <button onclick="document.getElementById('modal-tambah-exam').classList.remove('hidden')" class="bg-black text-white px-4 py-2 rounded-md shadow hover:bg-gray-800">
            Tambah Exam
        </button>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table id="examsTable" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Judul</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jurusan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Access Code</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Mode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Soal</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Durasi (mnt)</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jadwal</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($exams as $exam)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $exam->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $exam->jurusan }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $exam->access_code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ ucfirst($exam->selection_mode) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $exam->total_questions }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $exam->duration_minutes }}</td>
                        <td class="px-6 py-4 text-gray-700">
                            <div class="text-xs text-gray-600">
                                Mulai: {{ $exam->start_at ? $exam->start_at->format('d/m/Y H:i') : '-' }}<br>
                                Selesai: {{ $exam->end_at ? $exam->end_at->format('d/m/Y H:i') : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ ucfirst($exam->status) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button
                                    type="button"
                                    class="text-blue-600 hover:text-blue-900 border border-blue-600 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    onclick='openEditModal(@json($exam->load("questions")))'>
                                    Edit
                                </button>
                                <form id="deleteForm{{ $exam->id }}" action="{{ route('exams.destroy', $exam) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="button"
                                        onclick="confirmDelete('{{ $exam->id }}')"
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
    <div id="modal-tambah-exam" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 hidden">
        <div class="min-h-screen flex items-center justify-center py-6 px-4">
            <div class="bg-white w-full max-w-5xl mx-auto rounded-lg shadow-lg p-6 relative">
                <button onclick="document.getElementById('modal-tambah-exam').classList.add('hidden')" class="absolute top-4 right-4 text-xl font-bold text-gray-600 hover:text-gray-800">&times;</button>

                <h2 class="text-lg font-semibold mb-4">Tambah Exam</h2>

                <form action="{{ route('exams.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Judul *</label>
                            <input type="text" name="title" value="{{ old('title') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                            @error('title')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jurusan *</label>
                            <select name="jurusan" id="addJurusan" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white" onchange="filterQuestions('add')">
                                <option value="" disabled {{ old('jurusan') ? '' : 'selected' }}>Pilih jurusan</option>
                                @foreach ($majors as $jurusan)
                                    <option value="{{ $jurusan }}" {{ old('jurusan') === $jurusan ? 'selected' : '' }}>{{ $jurusan }}</option>
                                @endforeach
                            </select>
                            @error('jurusan')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Durasi (menit) *</label>
                            <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 100) }}" min="1" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                            @error('duration_minutes')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mulai</label>
                            <input type="datetime-local" name="start_at" value="{{ old('start_at') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                            @error('start_at')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Selesai</label>
                            <input type="datetime-local" name="end_at" value="{{ old('end_at') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                            @error('end_at')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mode Pemilihan Soal *</label>
                            <select name="selection_mode" id="addSelectionMode" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white" onchange="toggleQuestionBlock('add')">
                                <option value="" disabled {{ old('selection_mode') ? '' : 'selected' }}>Pilih mode</option>
                                <option value="manual" {{ old('selection_mode') === 'manual' ? 'selected' : '' }}>Manual</option>
                                <option value="automatic" {{ old('selection_mode') === 'automatic' ? 'selected' : '' }}>Automatic</option>
                            </select>
                            @error('selection_mode')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total Soal *</label>
                            <input type="number" name="total_questions" id="addTotalQuestions" value="{{ old('total_questions') }}" min="1" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                            @error('total_questions')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status *</label>
                            <select name="status" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white">
                                <option value="" disabled {{ old('status') ? '' : 'selected' }}>Pilih status</option>
                                @foreach (['draft', 'published'] as $status)
                                    <option value="{{ $status }}" {{ old('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="addQuestionBlock" class="{{ old('selection_mode') === 'manual' ? '' : 'hidden' }} border border-gray-200 rounded-md p-3">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-gray-700">Pilih soal (sesuai total soal)</p>
                            <span class="text-xs text-gray-500">Mode Manual</span>
                        </div>
                        <div class="border border-gray-200 rounded max-h-64 overflow-y-auto divide-y divide-gray-100">
                            @foreach ($questions as $question)
                                <label class="flex items-start space-x-2 p-2 question-item" data-jurusan="{{ $question->jurusan }}">
                                    <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" class="mt-1"
                                        {{ in_array($question->id, old('question_ids', [])) ? 'checked' : '' }}>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-800">{{ \Illuminate\Support\Str::limit($question->question_text, 120) }}</div>
                                        <div class="text-xs text-gray-500">{{ $question->jurusan }} • {{ strtoupper($question->type) }} • Poin {{ $question->points }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('question_ids')
                            <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="resetAddForm(); document.getElementById('modal-tambah-exam').classList.add('hidden')" class="px-4 py-2 rounded-md border text-sm">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="modal-edit-exam" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 hidden">
        <div class="min-h-screen flex items-center justify-center py-6 px-4">
            <div class="bg-white w-full max-w-5xl mx-auto rounded-lg shadow-lg p-6 relative">
                <button onclick="document.getElementById('modal-edit-exam').classList.add('hidden')" class="absolute top-4 right-4 text-xl font-bold text-gray-600 hover:text-gray-800">&times;</button>
                <h2 class="text-lg font-semibold mb-4">Edit Exam</h2>

                <form id="editExamForm" method="POST" action="{{ route('exams.update', ['exam' => '__ID__']) }}">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Judul *</label>
                                <input type="text" name="title" id="editTitle" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                                @error('title')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jurusan *</label>
                                <select name="jurusan" id="editJurusan" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white" onchange="filterQuestions('edit')">
                                    <option value="" disabled selected>Pilih jurusan</option>
                                    @foreach ($majors as $jurusan)
                                        <option value="{{ $jurusan }}">{{ $jurusan }}</option>
                                    @endforeach
                                </select>
                                @error('jurusan')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Durasi (menit) *</label>
                                <input type="number" name="duration_minutes" id="editDuration" min="1" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                                @error('duration_minutes')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mulai</label>
                                <input type="datetime-local" name="start_at" id="editStartAt" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                                @error('start_at')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Selesai</label>
                                <input type="datetime-local" name="end_at" id="editEndAt" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                                @error('end_at')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md-grid-cols-3 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mode Seleksi *</label>
                                <select name="selection_mode" id="editSelectionMode" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white" onchange="toggleQuestionBlock('edit')">
                                    <option value="" disabled selected>Pilih mode</option>
                                    <option value="manual">Manual</option>
                                    <option value="automatic">Automatic</option>
                                </select>
                                @error('selection_mode')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Soal *</label>
                                <input type="number" name="total_questions" id="editTotalQuestions" min="1" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                                @error('total_questions')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" id="editStatus" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white">
                                    <option value="" disabled selected>Pilih status</option>
                                    @foreach (['draft', 'published'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="editQuestionBlock" class="hidden border border-gray-200 rounded-md p-3">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-gray-700">Pilih soal (sesuai total soal)</p>
                                <span class="text-xs text-gray-500">Mode Manual</span>
                            </div>
                            <div class="border border-gray-200 rounded max-h-64 overflow-y-auto divide-y divide-gray-100">
                                @foreach ($questions as $question)
                                    <label class="flex items-start space-x-2 p-2 question-item-edit" data-jurusan="{{ $question->jurusan }}">
                                        <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" class="mt-1">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-800">{{ \Illuminate\Support\Str::limit($question->question_text, 120) }}</div>
                                            <div class="text-xs text-gray-500">{{ $question->jurusan }} • {{ strtoupper($question->type) }} • Poin {{ $question->points }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('question_ids')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" onclick="resetEditForm(); document.getElementById('modal-edit-exam').classList.add('hidden')" class="px-4 py-2 rounded-md border text-sm">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @vite(['resources/js/app.js'])
    <script>
        function toggleQuestionBlock(prefix) {
            const mode = document.getElementById(`${prefix}SelectionMode`).value;
            const block = document.getElementById(`${prefix}QuestionBlock`);
            const checkboxes = block?.querySelectorAll('input[type="checkbox"]') ?? [];
            if (mode === 'manual') {
                block?.classList.remove('hidden');
                checkboxes.forEach(cb => cb.disabled = false);
            } else {
                block?.classList.add('hidden');
                checkboxes.forEach(cb => cb.checked = false);
                checkboxes.forEach(cb => cb.disabled = true);
            }
        }

        function filterQuestions(prefix) {
            const jurusan = document.getElementById(`${prefix}Jurusan`).value;
            const items = document.querySelectorAll(prefix === 'add' ? '.question-item' : '.question-item-edit');
            items.forEach(item => {
                const show = !jurusan || item.dataset.jurusan === jurusan;
                item.classList.toggle('hidden', !show);
                if (!show) {
                    const cb = item.querySelector('input[type="checkbox"]');
                    if (cb) cb.checked = false;
                }
            });
        }

        function openEditModal(exam, overrides = {}) {
            const modal = document.getElementById('modal-edit-exam');
            modal.classList.remove('hidden');

            const form = document.getElementById('editExamForm');
            const baseAction = form.dataset.action ?? form.getAttribute('action');
            form.dataset.action = baseAction;
            form.action = baseAction.replace('__ID__', exam.id);

            document.getElementById('editTitle').value = overrides.title ?? exam.title ?? '';
            setSelectValueCaseInsensitive(document.getElementById('editJurusan'), overrides.jurusan ?? exam.jurusan ?? '');

            document.getElementById('editDuration').value = overrides.duration_minutes ?? exam.duration_minutes ?? 0;
            document.getElementById('editStartAt').value = formatDatetimeLocal(overrides.start_at ?? exam.start_at ?? '');
            document.getElementById('editEndAt').value = formatDatetimeLocal(overrides.end_at ?? exam.end_at ?? '');

            document.getElementById('editSelectionMode').value = overrides.selection_mode ?? exam.selection_mode ?? '';
            document.getElementById('editTotalQuestions').value = overrides.total_questions ?? exam.total_questions ?? '';
            setSelectValueCaseInsensitive(document.getElementById('editStatus'), overrides.status ?? exam.status ?? '');

            toggleQuestionBlock('edit');
            filterQuestions('edit');

            const selectedQuestions = overrides.question_ids ?? (exam.questions ? exam.questions.map(q => q.id) : []);
            const checkboxes = document.querySelectorAll('.question-item-edit input[type="checkbox"]');
            checkboxes.forEach(cb => {
                cb.checked = selectedQuestions.includes(Number(cb.value)) || selectedQuestions.includes(cb.value);
                cb.disabled = document.getElementById('editSelectionMode').value !== 'manual';
            });
        }

        function formatDatetimeLocal(value) {
            if (!value) return '';
            const date = new Date(value);
            if (isNaN(date.getTime())) return '';
            const pad = (n) => n.toString().padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
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
            const form = document.querySelector('#modal-tambah-exam form');
            form.reset();
            toggleQuestionBlock('add');
            filterQuestions('add');
        }

        function resetEditForm() {
            const form = document.getElementById('editExamForm');
            form.reset();
            toggleQuestionBlock('edit');
            filterQuestions('edit');
        }

        document.addEventListener('DOMContentLoaded', function () {
            toggleQuestionBlock('add');
            filterQuestions('add');
            @if($errors->any() && session('openModal') === 'add')
                document.getElementById('modal-tambah-exam').classList.remove('hidden');
                toggleQuestionBlock('add');
            @endif
        });

        @if(session('editExam'))
            window.onload = function() {
                const examData = @json(session('editExam'));
                openEditModal(examData, {
                    title: @json(old('title')),
                    jurusan: @json(old('jurusan')),
                    duration_minutes: @json(old('duration_minutes')),
                    start_at: @json(old('start_at')),
                    end_at: @json(old('end_at')),
                    selection_mode: @json(old('selection_mode')),
                    total_questions: @json(old('total_questions')),
                    status: @json(old('status')),
                    question_ids: @json(old('question_ids')),
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
