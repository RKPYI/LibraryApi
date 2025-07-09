<?php

namespace App\Services;

use App\Models\Book;
use App\Repositories\Contracts\BookRepositoryInterface;
use Illuminate\Http\UploadedFile;

class BookService
{
    protected BookRepositoryInterface $bookRepo;

    public function __construct(BookRepositoryInterface $bookRepo)
    {
        $this->bookRepo = $bookRepo;
    }

    public function getAll()
    {
        return $this->bookRepo->getAll();
    }

    public function details($id)
    {
        return $this->bookRepo->details($id);
    }

    public function create(array $data)
    {
        $categoryIds = $data['categories'] ?? [];
        unset($data['categories']);

        if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
            $data['cover_image'] = $data['cover_image']->store('book_covers', 'public');
        } else {
            $data['cover_image'] = null;
        }

        if (!empty($categoryIds)) {
            return $this->bookRepo->createWithCategories($data, $categoryIds);
        }

        return $this->bookRepo->create($data);
    }

    public function update(Book $book, array $data)
    {
        $categoryIds = $data['categories'] ?? null;
        unset($data['categories']);

        if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
            $data['cover_image'] = $data['cover_image']->store('book_covers', 'public');
        } elseif (isset($data['cover_image']) && $data['cover_image'] === null) {
            $data['cover_image'] = null;
        }

        if (!empty($categoryIds)) {
            return $this->bookRepo->updateWithCategories($book, $data, $categoryIds);
        }

        return $this->bookRepo->update($book, $data);
    }

    public function delete(Book $book)
    {
        return $this->bookRepo->delete($book);
    }

    public function search($query)
    {
        return $this->bookRepo->search($query);
    }
}
