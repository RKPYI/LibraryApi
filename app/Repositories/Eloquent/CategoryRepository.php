<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAll()
    {
        return Category::all();
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data)
    {
        $category->update($data);
        return $category;
    }

    public function delete(Category $category)
    {
        return $category->delete();
    }
}
