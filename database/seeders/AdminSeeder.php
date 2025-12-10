<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create(
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'), // Use bcrypt for hashing passwords
            ]
        );
        $user->userDetail()->create(
            [
                'user_id' => $user->id,
                'phone' => '0123456789',
                'program_id' => 1,
                'semester' => null,
                'intake' => null,
                'section' => null,
                'student_id' => 1111111,
                'cgpa' => null,
                
            ]
        );

        // Assign roles
        $user->assignRole('super_admin', 'admin', 'user', 'student');
    }
}
