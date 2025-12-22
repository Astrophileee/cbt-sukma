<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $guruRole = Role::firstOrCreate(['name' => 'guru']);
        $siswaRole = Role::firstOrCreate(['name' => 'siswa']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole($adminRole);

        $guru = User::firstOrCreate(
            ['email' => 'guru@example.com'],
            [
                'name' => 'Guru',
                'password' => bcrypt('password'),
            ]
        );
        $guru->assignRole($guruRole);

        $siswaUser = User::firstOrCreate(
            ['email' => 'siswa@example.com'],
            [
                'name' => 'Siswa User',
                'password' => bcrypt('password'),
            ]
        );
        $siswaUser->assignRole($siswaRole);

        Student::firstOrCreate(
            ['user_id' => $siswaUser->id],
            [
                'phone_number' => '081234567891',
                'no_peserta' => 'STU123',
                'major' => 'jepang',
                'address' => 'address student'
            ]
        );
    }
}
