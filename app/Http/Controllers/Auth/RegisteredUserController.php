<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    private array $majors = ['Taiwan', 'Polandia', 'Hongkong', 'Jepang', 'Korea', 'Turkey', 'Malaysia', 'Singapura'];

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', ['majors' => $this->majors]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'major' => ['required', 'string', Rule::in($this->majors)],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $studentRole = Role::firstOrCreate(['name' => 'siswa']);
            $user->syncRoles([$studentRole]);

            Student::create([
                'user_id' => $user->id,
                'no_peserta' => $this->generateNoPeserta(),
                'major' => $validated['major'],
                'phone_number' => $validated['phone_number'],
                'address' => $validated['address'],
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Generate unique no_peserta for student.
     */
    private function generateNoPeserta(): string
    {
        do {
            $noPeserta = now()->format('ymdHis') . rand(100, 999);
        } while (Student::where('no_peserta', $noPeserta)->exists());

        return $noPeserta;
    }
}
