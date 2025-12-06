<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Change program field to foreign key
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('set null');

            // University student fields
            $table->string('semester')->nullable();
            $table->integer('intake')->nullable();
            $table->integer('section')->nullable();
            $table->string('student_id')->nullable()->unique();
            $table->decimal('cgpa', 3, 2)->nullable();

            // Faculty fields
            $table->string('department')->nullable();
            $table->string('faculty_code')->nullable()->unique();
            $table->string('designation')->nullable();

            // Common fields
            $table->string('phone')->nullable();
            $table->string('profile_picture')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id']);
            $table->index(['student_id']);
            $table->index(['faculty_code']);
            $table->index(['program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};