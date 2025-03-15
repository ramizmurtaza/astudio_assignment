<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Job;
use App\Models\Language;
use App\Models\Location;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\JobAttributeValue;
use Faker\Factory as Faker;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        
        $faker = Faker::create();

        // Seed Languages with insertOrIgnore to avoid duplicates
        $languages = ['PHP', 'JavaScript', 'Python', 'Ruby', 'Go', 'Swift', 'Kotlin'];
        $languageData = array_map(fn($lang) => ['name' => $lang], $languages);
        Language::insertOrIgnore($languageData);

        // Seed Locations
        $locations = [
            ['city' => 'New York', 'state' => 'NY', 'country' => 'USA'],
            ['city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['city' => 'London', 'state' => 'England', 'country' => 'UK'],
            ['city' => 'Berlin', 'state' => 'Berlin', 'country' => 'Germany'],
            ['city' => 'Toronto', 'state' => 'Ontario', 'country' => 'Canada']
        ];
        Location::insert($locations);

        // Seed Categories with insertOrIgnore to avoid duplicates
        $categories = ['Software Engineering', 'Data Science', 'DevOps', 'Cybersecurity', 'Product Management'];
        $categoryData = array_map(fn($cat) => ['name' => $cat], $categories);
        Category::insertOrIgnore($categoryData);
    
        // Seed Attributes
        $attributes = [
            ['name' => 'years_experience', 'type' => 'number', 'options' => null],
            ['name' => 'remote_work_allowed', 'type' => 'boolean', 'options' => null],
            ['name' => 'contract_length', 'type' => 'text', 'options' => null],
            ['name' => 'preferred_language', 'type' => 'select', 'options' => ['English', 'Spanish', 'French']], // Array for select type
        ];
        
        // Ensure that select options are JSON-encoded before saving
        $attributes = array_map(function ($attribute) {
            if ($attribute['type'] === 'select' && is_array($attribute['options'])) {
                $attribute['options'] = json_encode($attribute['options']); // Encode array to JSON string
            }
            return $attribute;
        }, $attributes);
        
        // Bulk insert attributes into the database
        Attribute::insert($attributes);
   
        // Seed 500 Jobs
        for ($i = 0; $i < 100; $i++) {
            $job = Job::create([
                'title'        => $faker->jobTitle,
                'description'  => $faker->paragraph,
                'company_name' => $faker->company,
                'salary_min'   => $faker->numberBetween(40000, 70000),
                'salary_max'   => $faker->numberBetween(70000, 120000),
                'is_remote'    => $faker->boolean,
                'job_type'     => $faker->randomElement(['full-time', 'part-time', 'contract', 'freelance']),
                'status'       => $faker->randomElement(['draft', 'published', 'archived']),
                'published_at' => $faker->dateTimeThisYear
            ]);

            // Attach random languages, locations, and categories
            $job->languages()->attach(Language::inRandomOrder()->limit(rand(1, 3))->pluck('id')->toArray());
            $job->locations()->attach(Location::inRandomOrder()->limit(rand(1, 2))->pluck('id')->toArray());
            $job->categories()->attach(Category::inRandomOrder()->limit(rand(1, 2))->pluck('id')->toArray());

            // Add random attribute values
            foreach (Attribute::all() as $attr) {
                // Check if 'options' is a string and decode it if needed
                $options = is_string($attr->options) ? json_decode($attr->options, true) : $attr->options;

                JobAttributeValue::create([
                    'job_id'      => $job->id,
                    'attribute_id' => $attr->id,
                    'value'       => $attr->type === 'number' ? rand(1, 10) :
                                    ($attr->type === 'boolean' ? $faker->boolean :
                                    ($attr->type === 'select' && $options ? $faker->randomElement($options) :
                                    $faker->word))
                ]);
            }
        }
    }
}
