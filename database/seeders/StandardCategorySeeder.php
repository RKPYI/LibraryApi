<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StandardCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Fiction', 'description' => 'Books that contain stories created from the imagination.'],
            ['name' => 'Non-Fiction', 'description' => 'Books that are based on real events and facts.'],
            ['name' => 'Science Fiction', 'description' => 'Books that explore futuristic concepts and advanced technology.'],
            ['name' => 'Fantasy', 'description' => 'Books that contain magical elements and fantastical worlds.'],
            ['name' => 'Mystery', 'description' => 'Books that involve solving a crime or uncovering secrets.'],
            ['name' => 'Biography', 'description' => 'Books that tell the life story of a person.'],
            ['name' => 'History', 'description' => 'Books that cover historical events and figures.'],
            ['name' => 'Self-Help', 'description' => 'Books that provide guidance on personal development and improvement.'],
        ];

        foreach ($categories as $category) {
            Category::updateOrInsert(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
