@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Daftar User</h1>
        <button onclick="document.getElementById('modal-tambah-user').classList.remove('hidden')" class="bg-black text-white px-4 py-2 rounded-md shadow hover:bg-gray-800">
            Tambah
        </button>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table id="usersTable" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($users as $user)
                    <tr>
                        <td class="whitespace-nowrap text-gray-700">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $user->roles->pluck('name')->implode(', ') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <!-- Tombol Edit -->
                                <button
                                    type="button"
                                    class="text-blue-600 hover:text-blue-900 border border-blue-600 rounded-md px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    onclick='openEditModal(@json($user))'>
                                    Edit
                                </button>

                                <!-- Tombol Delete -->
                                <form id="deleteForm{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="button"
                                        onclick="confirmDelete('{{ $user->id }}')"
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
<div id="modal-tambah-user" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 hidden">
    <div class="min-h-screen flex items-center justify-center py-6 px-4">
        <div class="bg-white w-full max-w-md mx-auto rounded-lg shadow-lg p-6 relative">
            <!-- Close button -->
            <button onclick="document.getElementById('modal-tambah-user').classList.add('hidden')" class="absolute top-4 right-4 text-xl font-bold text-gray-600 hover:text-gray-800">&times;</button>

            <h2 class="text-lg font-semibold mb-4">Tambah User</h2>

                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <!-- Nama -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                        @error('name')
                            <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                        @error('email')
                            <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role *</label>
                        <select name="role" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white">
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih role</option>
                            @foreach (['admin', 'guru'] as $role)
                                <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Action -->
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="resetForm(); document.getElementById('modal-tambah-user').classList.add('hidden')" class="px-4 py-2 rounded-md border text-sm">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Simpan</button>
                    </div>
                </form>

        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit-user" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 hidden">
    <div class="min-h-screen flex items-center justify-center py-6 px-4">
        <div class="bg-white w-full max-w-md mx-auto rounded-lg shadow-lg p-6 relative">
            <button onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="absolute top-4 right-4 text-xl font-bold text-gray-600 hover:text-gray-800">&times;</button>
            <h2 class="text-lg font-semibold mb-4">Edit Pengguna</h2>

            <form id="editUserForm" method="POST" enctype="multipart/form-data"
            action="{{ route('users.update', ['user' => '__ID__']) }}">
                @csrf
                @method('PATCH')

                <!-- Nama -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama *</label>
                    <input type="text" name="name" id="editName" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                    @error('name')
                        <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" id="editEmail" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm">
                    @error('email')
                        <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role *</label>
                    <select name="role" id="editRole" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm bg-white">
                        <option value="" disabled selected>Pilih role</option>
                        @foreach (['admin', 'guru'] as $role)
                            <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                    @error('role')
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

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="resetForm(); document.getElementById('modal-edit-user').classList.add('hidden')" class="px-4 py-2 rounded-md border text-sm">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


@vite(['resources/js/app.js'])
<script>
    function openEditModal(user, overrides = {}) {
        const modal = document.getElementById('modal-edit-user');
        modal.classList.remove('hidden');

        const form = document.getElementById('editUserForm');
        const baseAction = form.dataset.action ?? form.getAttribute('action');
        form.dataset.action = baseAction;
        form.action = baseAction.replace('__ID__', user.id);

        document.getElementById('editName').value = overrides.name ?? user.name ?? '';
        document.getElementById('editEmail').value = overrides.email ?? user.email ?? '';
        const editRoleSelect = document.getElementById('editRole');
        const selectedRole = overrides.role ?? ((user.roles && user.roles.length) ? user.roles[0].name : '');
        editRoleSelect.value = selectedRole || '';
        document.getElementById('editPassword').value = '';
    }

function confirmDelete(userId) {
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
            document.getElementById('deleteForm' + userId).submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    @if($errors->any())
        document.getElementById('modal-tambah-user').classList.remove('hidden');
    @endif
});

function resetForm() {
    const form = document.querySelector('#modal-tambah-user form');
    form.reset();
}

document.querySelector('#modal-tambah-user .absolute').addEventListener('click', function() {
    resetForm();
    document.getElementById('modal-tambah-user').classList.add('hidden');
});



</script>

@if (session('success') || session('error'))
    <div id="flash-message"
        data-type="{{ session('success') ? 'success' : 'error' }}"
        data-message="{{ session('success') ?? session('error') }}">
    </div>
@endif

@if(session('editUser'))
    <script>
        window.onload = function() {
            openEditModal(@json(session('editUser')), {
                name: @json(old('name')),
                email: @json(old('email')),
                role: @json(old('role')),
            });
        }
    </script>
@endif



@endsection
