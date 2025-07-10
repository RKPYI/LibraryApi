<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'publisher' => 'Charles Scribner\'s Sons',
                'published_date' => '1925-04-10',
                'isbn' => '9780743273565',
                'description' => 'The Great Gatsby is a 1925 novel by American writer F. Scott Fitzgerald.',
                'stock' => 10,
                'cover_image' => 'https://picsum.photos/seed/book1/200/300',
            ],
            [
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'publisher' => 'J. B. Lippincott & Co.',
                'published_date' => '1960-07-11',
                'isbn' => '9780061120084',
                'description' => 'To Kill a Mockingbird is a novel by Harper Lee published in 1960.',
                'stock' => 5,
                'cover_image' => 'https://picsum.photos/seed/book2/200/300',
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'publisher' => 'Secker & Warburg',
                'published_date' => '1949-06-08',
                'isbn' => '9780451524935',
                'description' => 'Nineteen Eighty-Four: A Novel, often published as 1984, is a dystopian social science fiction novel by English novelist George Orwell.',
                'stock' => 7,
                'cover_image' => 'https://picsum.photos/seed/book3/200/300',
            ],
            [
                'title' => 'The Catcher in the Rye',
                'author' => 'J. D. Salinger',
                'publisher' => 'Little, Brown and Company',
                'published_date' => '1951-07-16',
                'isbn' => '9780316769488',
                'description' => 'The Catcher in the Rye is a novel by J. D. Salinger, partially published in serial form in 1945–1946 and as a novel in 1951.',
                'stock' => 3,
                'cover_image' => 'https://picsum.photos/seed/book4/200/300',
            ],
            [
                'title' => 'The Lord of the Rings',
                'author' => 'J. R. R. Tolkien',
                'publisher' => 'Allen & Unwin',
                'published_date' => '1954-07-29',
                'isbn' => '9780618640157',
                'description' => 'The Lord of the Rings is an epic high-fantasy novel by the English author and scholar J. R. R. Tolkien.',
                'stock' => 8,
                'cover_image' => 'https://picsum.photos/seed/book5/200/300',
            ],
        ];

        foreach ($books as $book) {
            Book::create($book);
        }
    }
}
