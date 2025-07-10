<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name', // Nama kategori
        'description', // Deskripsi kategori, nullable
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_category');
    }
}
