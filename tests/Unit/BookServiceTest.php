<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Repositories\Contracts\BookRepositoryInterface;
use App\Services\BookService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookServiceTest extends TestCase
{
    protected $bookRepoMock;
    protected $bookService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookRepoMock = Mockery::mock(BookRepositoryInterface::class);
        $this->bookService = new BookService($this->bookRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_get_all_books()
    {
        $this->bookRepoMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn(new Collection([new Book(), new Book()]));

        $books = $this->bookService->getAll();

        $this->assertInstanceOf(Collection::class, $books);
        $this->assertCount(2, $books);
    }

    #[Test]
    public function it_can_get_book_details()
    {
        $book = new Book();
        $book->setRawAttributes(['id' => 1, 'title' => 'Test Book']);
        $this->bookRepoMock
            ->shouldReceive('details')
            ->once()
            ->with(1)
            ->andReturn($book);

        $foundBook = $this->bookService->details(1);

        $this->assertInstanceOf(Book::class, $foundBook);
        $this->assertEquals(1, $foundBook->id);
    }

    #[Test]
    public function it_can_create_a_book_without_categories_or_image()
    {
        $data = [
            'title' => 'New Book',
            'author' => 'Author',
            'categories' => [],
        ];

        $this->bookRepoMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) use ($data) {
                return $arg['title'] === $data['title'] && $arg['cover_image'] === null;
            }))
            ->andReturn(new Book($data));

        $book = $this->bookService->create($data);

        $this->assertInstanceOf(Book::class, $book);
        $this->assertEquals('New Book', $book->title);
    }

    #[Test]
    public function it_can_create_a_book_with_categories()
    {
        $data = [
            'title' => 'New Book',
            'author' => 'Author',
            'categories' => [1, 2],
        ];

        $this->bookRepoMock
            ->shouldReceive('createWithCategories')
            ->once()
            ->with(Mockery::on(function ($bookData) {
                return $bookData['title'] === 'New Book';
            }), [1, 2])
            ->andReturn(new Book($data));

        $book = $this->bookService->create($data);

        $this->assertInstanceOf(Book::class, $book);
    }

    #[Test]
    public function it_can_create_a_book_with_a_cover_image()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('file.jpg', 100, 'image/jpeg');

        $storedPath = $file->store('book_covers', 'public');

        $data = [
            'title' => 'New Book',
            'author' => 'Author',
            'categories' => [],
            'cover_image' => $file,
        ];

        $this->bookRepoMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['title'] === 'New Book' && str_starts_with($arg['cover_image'], 'book_covers/');
            }))
            ->andReturn(new Book([
                'title' => 'New Book',
                'author' => 'Author',
                'cover_image' => $storedPath,
            ]));

        $book = $this->bookService->create($data);

        $this->assertInstanceOf(Book::class, $book);
        $this->assertStringContainsString('book_covers/', $book->cover_image);
    }

    #[Test]
    public function it_can_update_a_book()
    {
        $book = new Book(['id' => 1, 'title' => 'Old Title']);
        $data = ['title' => 'New Title'];

        $this->bookRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($book, $data)
            ->andReturn($book->fill($data));

        $updatedBook = $this->bookService->update($book, $data);

        $this->assertEquals('New Title', $updatedBook->title);
    }

    #[Test]
    public function it_can_update_a_book_with_categories()
    {
        $book = new Book(['id' => 1, 'title' => 'A Book']);
        $data = [
            'title' => 'An Updated Book',
            'categories' => [1, 2],
        ];

        $this->bookRepoMock
            ->shouldReceive('updateWithCategories')
            ->once()
            ->with($book, Mockery::on(function ($bookData) {
                return $bookData['title'] === 'An Updated Book';
            }), [1, 2])
            ->andReturn($book->fill($data));

        $updatedBook = $this->bookService->update($book, $data);

        $this->assertEquals('An Updated Book', $updatedBook->title);
    }

    #[Test]
    public function it_can_delete_a_book()
    {
        $book = new Book(['id' => 1]);
        $this->bookRepoMock
            ->shouldReceive('delete')
            ->once()
            ->with($book)
            ->andReturn(true);

        $result = $this->bookService->delete($book);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_can_search_for_books()
    {
        $query = 'Laravel';
        $this->bookRepoMock
            ->shouldReceive('search')
            ->once()
            ->with($query)
            ->andReturn(new Collection([new Book(['title' => 'Laravel Up & Running'])]));

        $books = $this->bookService->search($query);

        $this->assertCount(1, $books);
        $this->assertEquals('Laravel Up & Running', $books->first()->title);
    }
}
