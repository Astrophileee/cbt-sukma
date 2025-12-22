<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::with(['user.roles'])
            ->whereHas('user', fn ($query) => $query->role('siswa'))
            ->get();

        return view('students.index', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'major' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make('password'),
            ]);

            $user->syncRoles(['siswa']);

            Student::create([
                'user_id' => $user->id,
                'no_peserta' => $this->generateNoPeserta(),
                'major' => $validated['major'],
                'phone_number' => $validated['phone_number'],
                'address' => $validated['address'],
            ]);

            DB::commit();

            return redirect()->route('students.index')->with('success', 'Data siswa berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan data siswa.'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($student->user_id, 'id'),
            ],
            'password' => ['nullable', 'string'],
            'no_peserta' => [
                'required',
                'string',
                'max:255',
                Rule::unique('students', 'no_peserta')->ignore($student->id, 'id'),
            ],
            'major' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('students.index')
                ->withErrors($validator)
                ->withInput()
                ->with('editStudent', $student->load('user.roles'));
        }

        $data = $validator->validated();

        DB::transaction(function () use ($data, $student) {
            $updateUserData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (!empty($data['password'])) {
                $updateUserData['password'] = Hash::make($data['password']);
            }

            $student->user->update($updateUserData);

            $student->user->syncRoles(['siswa']);

            $student->update([
                'no_peserta' => $data['no_peserta'],
                'major' => $data['major'],
                'phone_number' => $data['phone_number'],
                'address' => $data['address'],
            ]);
        });

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        try {
            DB::transaction(function () use ($student) {
                $student->delete();
                $student->user()->delete();
            });

            return redirect()->route('students.index')->with('success', 'Data siswa berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()->route('students.index')->with('error', 'Data siswa tidak dapat dihapus karena masih digunakan di data/transaksi lain.');
        }
    }

    /**
     * Generate unique no_peserta.
     */
    private function generateNoPeserta(): string
    {
        do {
            $noPeserta = now()->format('ymdHis') . rand(100, 999);
        } while (Student::where('no_peserta', $noPeserta)->exists());

        return $noPeserta;
    }
}
