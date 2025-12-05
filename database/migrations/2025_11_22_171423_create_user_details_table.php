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

            // University student fields
            $table->string('semester')->nullable();
            $table->string('intake')->nullable();
            $table->string('program')->nullable();
            $table->string('student_id')->nullable()->unique();
            $table->decimal('cgpa', 3, 2)->nullable();

            // Faculty fields
            $table->string('department')->nullable();
            $table->string('faculty_id')->nullable()->unique();
            $table->string('designation')->nullable();
            $table->string('office_room')->nullable();
            $table->string('office_hours')->nullable();

            // Common fields
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('profile_picture')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id']);
            $table->index(['student_id']);
            $table->index(['faculty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};