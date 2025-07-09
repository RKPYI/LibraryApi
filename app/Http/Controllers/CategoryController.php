<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @OA\Get(
     *  path="/api/v1/categories",
     *  tags={"Categories"},
     *  summary="Get list of categories",
     *  @OA\Response(
     *      response=200,
     *      description="Categories retrieved successfully",
     *  )
     * )
     */
    public function index()
    {
        $categories = $this->categoryService->getAll();
        return $this->success($categories, 'Categories retrieved successfully');
    }

    /**
     * @OA\Post(
     *  path="/api/v1/categories",
     *  tags={"Categories"},
     *  summary="Create a new category",
     *  security={{"sanctum":{}}},
     *  @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          required={"name"},
     *          @OA\Property(property="name", type="string", example="Fiction"),
     *          @OA\Property(property="description", type="string", example="Fictional books")
     *      )
     *  ),
     *  @OA\Response(
     *      response=201,
     *      description="Category created successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=403,
     *      description="Unauthorized",
     *  ),
     *  @OA\Response(
     *      response=422,
     *      description="Validation error",
     *  )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = $this->categoryService->create($data);

        return $this->success($category, 'Category created successfully', 201);
    }

    /**
     * @OA\Get(
     *  path="/api/v1/categories/{category}",
     *  tags={"Categories"},
     *  summary="Get category details",
     *  @OA\Parameter(
     *      name="category",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Category retrieved successfully",
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Category not found",
     *  )
     * )
     */
    public function show(Category $category)
    {
        return $this->success($category, 'Category retrieved successfully');
    }

    /**
     * @OA\Put(
     *  path="/api/v1/categories/{category}",
     *  tags={"Categories"},
     *  summary="Update a category",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="category",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          required={"name"},
     *          @OA\Property(property="name", type="string", example="Fiction"),
     *          @OA\Property(property="description", type="string", example="Fictional books")
     *      )
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Category updated successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=403,
     *      description="Unauthorized",
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Category not found",
     *  ),
     *  @OA\Response(
     *      response=422,
     *      description="Validation error",
     *  )
     * )
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = $this->categoryService->update($category, $data);

        return $this->success($category, 'Category updated successfully');
    }

    /**
     * @OA\Delete(
     *  path="/api/v1/categories/{category}",
     *  tags={"Categories"},
     *  summary="Delete a category",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="category",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Response(
     *      response=204,
     *      description="Category deleted successfully",
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthenticated",
     *  ),
     *  @OA\Response(
     *      response=403,
     *      description="Unauthorized",
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Category not found",
     *  )
     * )
     */
    public function destroy(Category $category)
    {
        $this->categoryService->delete($category);
        return $this->success([], 'Category deleted successfully', 204);
    }
}
