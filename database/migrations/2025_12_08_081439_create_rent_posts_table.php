<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rent_posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');   // delete posts when user deleted
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['room', 'flat', 'single_bed', 'sublet']);
            $table->string('contact_number');
            $table->integer('rent');
            $table->string('location');
            $table->string('address_detail')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('washrooms')->nullable();
            $table->date('available_from')->nullable();
            $table->boolean('is_available')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_posts');
    }
};
