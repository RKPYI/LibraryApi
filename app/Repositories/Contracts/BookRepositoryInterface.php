<?php
namespace App\Repositories\Contracts;

use App\Models\Book;

interface BookRepositoryInterface
{
    public function getAll();
    public function details($id);
    public function create(array $data);
    public function createWithCategories(array $data, array $categoryIds);
    public function update(Book $book, array $data);
    public function updateWithCategories(Book $book, array $data, array $categoryIds);
    public function delete(Book $book);
    public function search($query);
}
