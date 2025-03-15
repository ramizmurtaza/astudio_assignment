<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Categories with insertOrIgnore to avoid duplicates
        $categories = ['Software Engineering', 'Data Science', 'DevOps', 'Cybersecurity', 'Product Management'];
        $categoryData = array_map(fn($cat) => ['name' => $cat], $categories);
        Category::insertOrIgnore($categoryData);
    }
}
