<?php

namespace App\Repositories\Eloquent;

use App\Models\Book;
use App\Repositories\Contracts\BookRepositoryInterface;

class BookRepository implements BookRepositoryInterface
{
    public function getAll()
    {
        return Book::with('categories')->get();
    }

    public function details($id)
    {
        return Book::with('categories')->find($id);
    }

    public function create(array $data)
    {
        return Book::create($data);
    }

    public function createWithCategories(array $data, array $categoryIds)
    {
        $book = Book::create($data);
        if (!empty($categoryIds)) {
            $book->categories()->attach($categoryIds);
        }

        return $book->load('categories');
    }

    public function update(Book $book, array $data)
    {
        $book->update($data);
        return $book;
    }

    public function updateWithCategories(Book $book, array $data, array $categoryIds)
    {
        $book->update($data);
        if (!empty($categoryIds)) {
            $book->categories()->sync($categoryIds);
        } else {
            $book->categories()->detach();
        }

        return $book->load('categories');
    }

    public function delete(Book $book)
    {
        $book->delete();
        return true;
    }

    public function search($query)
    {
        return Book::with('categories')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                ->orWhere('author', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->orWhere('publisher', 'like', "%{$query}%")
                ->orWhere('isbn', 'like', "%{$query}%")
                ->orWhere('published_date', 'like', "%{$query}%")
                ->orWhereHas('categories', function ($q2) use ($query) {
                    $q2->where('name', 'like', "%{$query}%")->distinct();
                });
            })
            ->get();
    }

}
