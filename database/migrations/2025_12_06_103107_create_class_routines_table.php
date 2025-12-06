<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('class_routines', function (Blueprint $table) {
            $table->id();

            // Basic info from your JSON
            $table->foreignId('program_id')->constrained()->onDelete('cascade')->index();
            $table->integer('intake')->index();
            $table->integer('section')->index();
            $table->string('semester')->index();

            // Day and time info
            $table->string('day'); // e.g., "MON", "TUE"
            $table->string('time_slot'); // e.g., "07:00 PM to 08:10 PM"
            $table->time('start_time');
            $table->time('end_time');

            $table->string('course_code'); // e.g., "ELT 5105FC"
            $table->string('course_name')->nullable(); // Optional, can be filled later
            $table->string('teacher_code'); // e.g., "KSI"
            $table->string('teacher_name')->nullable(); // Optional
            $table->string('room_number'); // e.g., "1409"
            $table->string('room_type')->nullable(); // e.g., "Classroom", "Lab"

            $table->enum('status', ['active', 'cancelled', 'rescheduled'])->default('active');
            $table->date('effective_date')->nullable();
            $table->integer('slot_order')->default(1); // For ordering multiple slots in same day

            $table->string('class_details'); // e.g., "ELT 5105FC: KSI R: 1409"

            $table->timestamps();

            // Indexes for faster queries
            $table->index(['program_id', 'intake', 'section', 'semester']);
            $table->index(['day', 'start_time']);
            $table->index(['teacher_code', 'day', 'start_time']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_routines');
    }
};