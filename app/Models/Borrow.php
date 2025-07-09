<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_BORROWED = 'borrowed';
    const STATUS_RETURNED = 'returned';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_LOST_REQUESTED = 'lost_requested';
    const STATUS_LOST = 'lost';
    const STATUS_RETURN_REQUESTED = 'return_requested';
    const STATUS_RETURN_REJECTED = 'return_rejected';

    protected $fillable = [
        'user_id', // Relasi ke user
        'book_id', // Relasi ke buku
        'borrow_date', // Tanggal peminjaman
        'due_date', // Tanggal jatuh tempo, nullable jika belum ditentukan
        'return_date', // Tanggal pengembalian, nullable jika belum dikembalikan
        'status', // Status peminjaman (borrowed, returned, overdue, cancelled)
        'notes', // Catatan tambahan, nullable
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
