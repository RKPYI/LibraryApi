<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id', // Relasi ke user
        'book_id', // Relasi ke buku
        'rating', // Rating dari 1 sampai 5
        'comment', // Komentar, nullable jika tidak ada
        'is_approved', // Status persetujuan, default false
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
