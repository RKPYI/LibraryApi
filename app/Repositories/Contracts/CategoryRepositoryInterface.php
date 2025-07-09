<?php
namespace App\Repositories\Contracts;

use App\Models\Category;

interface CategoryRepositoryInterface
{
    public function getAll();
    public function create(array $data);
    public function update(Category $category, array $data);
    public function delete(Category $category);
}
