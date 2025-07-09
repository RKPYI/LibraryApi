<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke user
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade'); // Relasi ke buku
            $table->integer('rating'); // Rating dari 1 sampai 5
            $table->text('comment')->nullable(); // Komentar, nullable jika tidak ada
            $table->boolean('is_approved')->default(false); // Status persetujuan, default false
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
