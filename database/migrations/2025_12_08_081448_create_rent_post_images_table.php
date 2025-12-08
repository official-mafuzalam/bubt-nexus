<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Schema::create('rent_post_images', function (Blueprint $table) {
        //     $table->id();

        //     $table->foreignId('rent_post_id')
        //         ->constrained('rent_posts')
        //         ->onDelete('cascade');   // delete images if post deleted

        //     $table->string('image_path');  // storage path

        //     $table->timestamps();
        // });
    }
    // Not used yet
    public function down(): void
    {
        Schema::dropIfExists('rent_post_images');
    }
};
