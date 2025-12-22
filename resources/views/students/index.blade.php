@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Daftar Siswa</h1>
        <button onclick="document.getElementById('modal-tambah-student').classList.remove('hidden')" class="bg-black text-white px-4 py-2 rounded-md shadow hover:bg-gray-800">
            Tambah
        </button>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table id="studentsTable" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No Peserta</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jurusan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No HP</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Alamat</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($students as $student)
                    <tr>
                        <td class="whitespace-nowrap text-gray-700">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $student->user->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $student->user->email ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $student->user->roles->pluck('name')->implode(', ') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $student->no_peserta }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $student->major }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $student->phone_number }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $student->address }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button
                                    type="button"
                                    class="text-blue-600 hover:text-blue-900 border border-blue-600 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    onclick='openEditModal(@json($student))'>
                                    Edit
                                </button>
                                <form id="deleteForm{{ $student->id }}" action="{{ route('students.destroy', $student) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="button"
                                        onclick="confirmDelete('{{ $student->id }}')"
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
    <div id="modal-tambah-student" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 hidden">
        <div class="min-h-screen flex items-center justify-center py-6 px-4">
            <div class="bg-white w-full max-w-2xl mx-auto rounded-lg shadow-lg p-6 relative">
                <button onclick="document.getElementById('modal-tambah-student').classList.add('hidden')" class="absolute top-4 right-4 text-xl font-bold text-gray-600 hover:text-gray-800">&times;</button>

                <h2 class="text-lg font-semibold mb-4">Tambah Siswa</h2>

                <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                            @error('name')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                            @error('email')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jurusan *</label>
                            <select name="major" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white">
                                <option value="" disabled {{ old('major') ? '' : 'selected' }}>Pilih jurusan</option>
                                @foreach (['Taiwan', 'Polandia', 'Hongkong', 'Jepang', 'Korea', 'Turkey', 'Malaysia', 'Singapura'] as $jurusan)
                                    <option value="{{ $jurusan }}" {{ old('major') === $jurusan ? 'selected' : '' }}>{{ $jurusan }}</option>
                                @endforeach
                            </select>
                            @error('major')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">No HP *</label>
                            <input type="text" name="phone_number" value="{{ old('phone_number') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                            @error('phone_number')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alamat *</label>
                        <textarea name="address" rows="3" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="resetAddForm(); document.getElementById('modal-tambah-student').classList.add('hidden')" class="px-4 py-2 rounded-md border text-sm">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Simpan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="modal-edit-student" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 hidden">
        <div class="min-h-screen flex items-center justify-center py-6 px-4">
            <div class="bg-white w-full max-w-2xl mx-auto rounded-lg shadow-lg p-6 relative">
                <button onclick="document.getElementById('modal-edit-student').classList.add('hidden')" class="absolute top-4 right-4 text-xl font-bold text-gray-600 hover:text-gray-800">&times;</button>
                <h2 class="text-lg font-semibold mb-4">Edit Siswa</h2>

                <form id="editStudentForm" method="POST" enctype="multipart/form-data"
                    action="{{ route('students.update', ['student' => '__ID__']) }}">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama *</label>
                            <input type="text" name="name" id="editName" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                            @error('name')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" name="email" id="editEmail" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                            @error('email')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">No Peserta</label>
                            <input type="text" name="no_peserta" id="editNoPeserta" value="{{ old('no_peserta') }}" readonly class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-gray-50 text-gray-600">
                            @error('no_peserta')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jurusan *</label>
                            <select name="major" id="editMajor" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white">
                                <option value="" disabled selected>Pilih jurusan</option>
                                @foreach (['Taiwan', 'Polandia', 'Hongkong', 'Jepang', 'Korea', 'Turkey', 'Malaysia', 'Singapura'] as $jurusan)
                                    <option value="{{ $jurusan }}">{{ $jurusan }}</option>
                                @endforeach
                            </select>
                            @error('major')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">No HP *</label>
                            <input type="text" name="phone_number" id="editPhone" value="{{ old('phone_number') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                            @error('phone_number')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password Baru (Opsional)</label>
                            <input type="password" name="password" id="editPassword" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm" placeholder="Kosongkan jika tidak ingin mengubah">
                            @error('password')
                                <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alamat *</label>
                        <textarea name="address" id="editAddress" rows="3" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="resetEditForm(); document.getElementById('modal-edit-student').classList.add('hidden')" class="px-4 py-2 rounded-md border text-sm">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    @vite(['resources/js/app.js'])
    <script>
        function openEditModal(student, overrides = {}) {
            const modal = document.getElementById('modal-edit-student');
            modal.classList.remove('hidden');

            const form = document.getElementById('editStudentForm');
            const baseAction = form.dataset.action ?? form.getAttribute('action');
            form.dataset.action = baseAction;
            form.action = baseAction.replace('__ID__', student.id);

            const user = student.user || {};

            document.getElementById('editName').value = overrides.name ?? user.name ?? '';
            document.getElementById('editEmail').value = overrides.email ?? user.email ?? '';

            document.getElementById('editNoPeserta').value = overrides.no_peserta ?? student.no_peserta ?? '';
            setSelectValueCaseInsensitive(document.getElementById('editMajor'), overrides.major ?? student.major ?? '');
            document.getElementById('editPhone').value = overrides.phone_number ?? student.phone_number ?? '';
            document.getElementById('editAddress').value = overrides.address ?? student.address ?? '';
            document.getElementById('editPassword').value = '';
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

        function confirmDelete(studentId) {
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
                    document.getElementById('deleteForm' + studentId).submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if($errors->any() && !session('editStudent'))
                document.getElementById('modal-tambah-student').classList.remove('hidden');
            @endif
        });

        function resetAddForm() {
            const form = document.querySelector('#modal-tambah-student form');
            form.reset();
        }

        function resetEditForm() {
            const form = document.getElementById('editStudentForm');
            form.reset();
        }

        document.querySelector('#modal-tambah-student .absolute').addEventListener('click', function() {
            resetAddForm();
            document.getElementById('modal-tambah-student').classList.add('hidden');
        });
    </script>

    @if (session('success') || session('error'))
        <div id="flash-message"
            data-type="{{ session('success') ? 'success' : 'error' }}"
            data-message="{{ session('success') ?? session('error') }}">
        </div>
    @endif

    @if(session('editStudent'))
        <script>
            window.onload = function() {
                openEditModal(@json(session('editStudent')), {
                    name: @json(old('name')),
                    email: @json(old('email')),
                    no_peserta: @json(old('no_peserta')),
                    major: @json(old('major')),
                    phone_number: @json(old('phone_number')),
                    address: @json(old('address')),
                });
            }
        </script>
    @endif
@endsection
