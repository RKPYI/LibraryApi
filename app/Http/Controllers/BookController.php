<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\BookService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    use ApiResponse;

    protected $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    /**
     * @OA\Get(
     *  path="/api/v1/books",
     *  tags={"Books"},
     *  summary="Get list of books",
     *  @OA\Response(
     *      response=200,
     *      description="Books retrieved successfully",
     *  )
     * )
     */
    public function index()
    {
        $books = $this->bookService->getAll();
        return $this->success($books, 'Books retrieved successfully');
    }

    /**
     * @OA\Post(
     *  path="/api/v1/books",
     *  tags={"Books"},
     *  summary="Create a new book",
     *  security={{"sanctum":{}}},
     *  @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          required={"title", "author", "published_at", "isbn"},
     *          @OA\Property(property="title", type="string", example="The Lord of the Rings"),
     *          @OA\Property(property="author", type="string", example="J.R.R. Tolkien"),
     *          @OA\Property(property="published_at", type="string", format="date", example="1954-07-29"),
     *          @OA\Property(property="isbn", type="string", example="978-0-618-64015-7"),
     *          @OA\Property(property="description", type="string", example="A fantasy novel."),
     *          @OA\Property(property="cover_image", type="string", format="binary"),
     *          @OA\Property(property="stock", type="integer", example=10),
     *          @OA\Property(property="categories", type="array", @OA\Items(type="integer", example=1))
     *      )
     *  ),
     *  @OA\Response(
     *      response=201,
     *      description="Book created successfully",
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
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'published_at' => 'required|date',
            'isbn' => 'required|string|max:13|unique:books,isbn',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'stock' => 'nullable|integer|min:0',

            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $book = $this->bookService->create($data);

        return $this->success($book, 'Book created successfully', 201);
    }

    /**
     * @OA\Get(
     *  path="/api/v1/books/{book}",
     *  tags={"Books"},
     *  summary="Get book details",
     *  @OA\Parameter(
     *      name="book",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Book retrieved successfully",
     *  ),
     *  @OA\Response(
     *      response=404,
     *      description="Book not found",
     *  )
     * )
     */
    public function show(Book $book)
    {
        $bookDetails = $this->bookService->details($book->id);
        return $this->success($bookDetails, 'Book retrieved successfully');
    }

    /**
     * @OA\Put(
     *  path="/api/v1/books/{book}",
     *  tags={"Books"},
     *  summary="Update a book",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="book",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          @OA\Property(property="title", type="string", example="The Lord of the Rings"),
     *          @OA\Property(property="author", type="string", example="J.R.R. Tolkien"),
     *          @OA\Property(property="published_at", type="string", format="date", example="1954-07-29"),
     *          @OA\Property(property="isbn", type="string", example="978-0-618-64015-7"),
     *          @OA\Property(property="description", type="string", example="A fantasy novel."),
     *          @OA\Property(property="cover_image", type="string", format="binary"),
     *          @OA\Property(property="stock", type="integer", example=10),
     *          @OA\Property(property="categories", type="array", @OA\Items(type="integer", example=1))
     *      )
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Book updated successfully",
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
     *      description="Book not found",
     *  ),
     *  @OA\Response(
     *      response=422,
     *      description="Validation error",
     *  )
     * )
     */
    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'published_at' => 'sometimes|required|date',
            'isbn' => [
                'sometimes|required',
                'string',
                'max:13',
                Rule::unique('books', 'isbn')->ignore($book->id),
            ],
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'stock' => 'nullable|integer|min:0',

            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $updatedBook = $this->bookService->update($book, $data);

        return $this->success($updatedBook, 'Book updated successfully');
    }

    /**
     * @OA\Delete(
     *  path="/api/v1/books/{book}",
     *  tags={"Books"},
     *  summary="Delete a book",
     *  security={{"sanctum":{}}},
     *  @OA\Parameter(
     *      name="book",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Response(
     *      response=204,
     *      description="Book deleted successfully",
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
     *      description="Book not found",
     *  )
     * )
     */
    public function destroy(Book $book)
    {
        $this->bookService->delete($book);
        return $this->success(null, 'Book deleted successfully', 204);
    }

    /**
     * @OA\Get(
     *  path="/api/v1/books/search",
     *  tags={"Books"},
     *  summary="Search for books",
     *  @OA\Parameter(
     *      name="q",
     *      in="query",
     *      required=true,
     *      @OA\Schema(type="string")
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Books retrieved successfully",
     *  )
     * )
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $books = $this->bookService->search($query);
        return $this->success($books, 'Books retrieved successfully');
    }
}
