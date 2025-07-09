<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryService
{
    protected CategoryRepositoryInterface $categoryRepo;

    public function __construct(CategoryRepositoryInterface $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    public function getAll()
    {
        return $this->categoryRepo->getAll();
    }

    public function create(array $data)
    {
        return $this->categoryRepo->create($data);
    }

    public function update(Category $category, array $data)
    {
        return $this->categoryRepo->update($category, $data);
    }

    public function delete(Category $category)
    {
        return $this->categoryRepo->delete($category);
    }

}
