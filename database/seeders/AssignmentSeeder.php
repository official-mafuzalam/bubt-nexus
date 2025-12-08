<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\Assignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // Create faculty
        $faculty = User::create([
            'name' => 'Professor John Doe',
            'email' => 'fc@example.com',
            'password' => Hash::make('password'),
        ]);
        $faculty->assignRole('faculty');

        // Create 5 students manually
        $students = [];

        for ($i = 1; $i <= 5; $i++) {
            $student = User::create([
                'name' => "Student $i",
                'email' => "student$i@example.com",
                'password' => Hash::make('password'),
            ]);

            $student->assignRole('student');
            $students[] = $student;
        }

        // Create class
        $class = ClassModel::create([
            'name' => 'Introduction to Programming',
            'code' => 'CSE101',
            'description' => 'Basic programming concepts using Python',
            'faculty_id' => $faculty->id,
            'subject_code' => 'CSE101',
            'semester' => 1,
            'section' => 'A',
            'enrollment_code' => ClassModel::generateEnrollmentCode(),
        ]);

        // Enroll students
        foreach ($students as $student) {
            $class->enrollments()->create([
                'student_id' => $student->id,
            ]);
        }

        // Create assignments
        Assignment::create([
            'class_id' => $class->id,
            'title' => 'First Python Program',
            'description' => 'Write a simple Python program that prints "Hello World"',
            'total_marks' => 10,
            'deadline' => now()->addWeek(),
            'status' => 'published',
        ]);

        Assignment::create([
            'class_id' => $class->id,
            'title' => 'Data Structures Assignment',
            'description' => 'Implement basic data structures in Python',
            'total_marks' => 20,
            'deadline' => now()->addWeeks(2),
            'status' => 'published',
        ]);
    }
}
