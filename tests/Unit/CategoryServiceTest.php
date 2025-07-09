<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\CategoryService;
use Mockery;
use PHPUnit\Framework\TestCase;

class CategoryServiceTest extends TestCase
{
    protected CategoryService $service;
    protected $mockRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepo = Mockery::mock(CategoryRepositoryInterface::class);

        $this->service = new CategoryService($this->mockRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_all_categories()
    {
        $data = [
            ['name' => 'Category 1', 'description' => 'Description 1'],
            ['name' => 'Category 2', 'description' => 'Description 2'],
            ['name' => 'Category 3', 'description' => 'Description 3'],
        ];

        $expectedCategories = collect($data)->map(function ($item) {
            return new Category($item);
        });

        $this->mockRepo
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($expectedCategories);

        $result = $this->service->getAll();

        $this->assertCount(3, $result);
        $this->assertInstanceOf(Category::class, $result->first());
    }

    public function test_create_category()
    {
        $data = [
            'name' => 'Test Name',
            'description' => 'This is a test category description.',
        ];

        $expectedCategory = new Category($data);

        $this->mockRepo
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expectedCategory);

        $result = $this->service->create($data);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals($expectedCategory->name, $result->name);
        $this->assertEquals($expectedCategory->description, $result->description);
    }

    public function test_update_category()
    {
        $data = [
            'name' => 'Updated Category',
            'description' => 'This is updated description.',
        ];

        $category = new Category($data);

        $this->mockRepo
            ->shouldReceive('update')
            ->once()
            ->with($category, $data)
            ->andReturn($category);

        $result = $this->service->update($category, $data);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals($category->name, $result->name);
    }

    public function test_delete_category()
    {
        $data = [
            'name' => 'Category to be deleted',
            'description' => 'This category will be deleted.',
        ];

        $category = new Category($data);

        $this->mockRepo
            ->shouldReceive('delete')
            ->once()
            ->with($category)
            ->andReturn(true);

        $result = $this->service->delete($category);

        $this->assertTrue($result);
    }
}
