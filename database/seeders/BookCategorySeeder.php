<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = Book::all();
        $categories = Category::pluck('id');

        if ($books->isEmpty() || $categories->isEmpty()) {
            $this->command->info('No books or categories to seed. Please run BookSeeder and StandardCategorySeeder first.');
            return;
        }

        $books->each(function (Book $book) use ($categories) {
            $book->categories()->attach(
                $categories->random(rand(1, 3))->toArray()
            );
        });
    }
}
