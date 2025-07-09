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
        Schema::create('borrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke user
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade'); // Relasi ke buku
            $table->date('borrow_date')->nullable(); // Tanggal
            $table->date('due_date')->nullable(); // Tanggal jatuh tempo, nullable jika belum ditentukan
            $table->date('return_date')->nullable(); // Tanggal pengembalian, nullable jika belum dikembalikan
            $table->enum('status', ['pending', 'borrowed', 'returned', 'overdue', 'cancelled', 'lost_requested', 'lost', 'return_rejected'])->default('pending'); // Status peminjaman
            $table->string('notes')->nullable(); // Catatan tambahan, nullable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrows');
    }
};
