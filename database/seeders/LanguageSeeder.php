<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Languages with insertOrIgnore to avoid duplicates
        $languages = ['PHP', 'JavaScript', 'Python', 'Ruby', 'Go', 'Swift', 'Kotlin'];
        $languageData = array_map(fn($lang) => ['name' => $lang], $languages);
        Language::insertOrIgnore($languageData);
    }
}
