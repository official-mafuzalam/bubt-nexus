<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            ['name' => 'BBA', 'code' => '001', 'description' => 'Bachelor of Business Administration'],
            ['name' => 'B.Sc. in CSE', 'code' => '006', 'description' => 'Bachelor of Science in Computer Science and Engineering'],
            ['name' => 'B.A (Hons) in English', 'code' => '007', 'description' => 'Bachelor of Arts (Honors) in English'],
            ['name' => 'LL.B (Hons)', 'code' => '009', 'description' => 'Bachelor of Laws (Honors)'],
            ['name' => 'B.Sc. Engg in CSE (Evening)', 'code' => '019', 'description' => 'Bachelor of Science in Computer Science and Engineering (Evening)'],
            ['name' => 'B.Sc. (Hons) in Economics', 'code' => '020', 'description' => 'Bachelor of Science (Honors) in Economics'],
            ['name' => 'B.Sc. in EEE', 'code' => '021', 'description' => 'Bachelor of Science in Electrical and Electronics Engineering'],
            ['name' => 'B.Sc. in Textile Engg.', 'code' => '022', 'description' => 'Bachelor of Science in Textile Engineering'],
            ['name' => 'B.Sc. in Civil Engg.', 'code' => '027', 'description' => 'Bachelor of Science in Civil Engineering'],
            ['name' => 'B.Sc. in EEE (Evening)', 'code' => '023', 'description' => 'Bachelor of Science in Electrical and Electronics Engineering (Evening)'],
            ['name' => 'B.Sc. in Textile Engg (Evening)', 'code' => '000', 'description' => 'Bachelor of Science in Textile Engineering (Evening)'],
            ['name' => 'B.Sc. in Civil Engg (Evening)', 'code' => '111', 'description' => 'Bachelor of Science in Civil Engineering (Evening)'],
            ['name' => 'MBA Regular', 'code' => '222', 'description' => 'Master of Business Administration (Regular)'],
            ['name' => 'Executive MBA', 'code' => '004', 'description' => 'Executive Master of Business Administration'],
            ['name' => 'MA in English', 'code' => '008', 'description' => 'Master of Arts in English'],
            ['name' => 'MA in ELT', 'code' => '016', 'description' => 'Master of Arts in English Language Teaching'],
            ['name' => 'LL.M', 'code' => '333', 'description' => 'Master of Laws'],
            ['name' => 'M.Sc in Economics', 'code' => '013', 'description' => 'Master of Science in Economics'],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }
    }
}