<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'publisher',
        'published_date',
        'isbn',
        'description',
        'stock',
        'cover_image', // Path to the cover image
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'book_category');
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
