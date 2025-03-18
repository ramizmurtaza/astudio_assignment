<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
    public function run(): void
    {
        $faker = Faker::create();
        
        // Fetch all attributes once to reduce queries
        $attributes = Attribute::all();
        
        DB::beginTransaction();
        try {
            // Seed 1000 Jobs
            for ($i = 0; $i < 1000; $i++) {
                
                // Create Job entry
                $job = Job::create([
                    'title'        => $faker->jobTitle,
                    'description'  => $faker->paragraph,
                    'company_name' => $faker->company,
                    'salary_min'   => $faker->numberBetween(40000, 70000),
                    'salary_max'   => $faker->numberBetween(70000, 120000),
                    'is_remote'    => $faker->boolean,
                    'job_type'     => $faker->randomElement(['full-time', 'part-time', 'contract', 'freelance']),
                    'status'       => $faker->randomElement(['draft', 'published', 'archived']),
                    'published_at' => $faker->dateTimeThisYear,
                ]);

                // Attach random languages, locations, and categories in bulk
                $job->languages()->attach(
                    Language::inRandomOrder()->limit(rand(1, 3))->pluck('id')->toArray()
                );
                $job->locations()->attach(
                    Location::inRandomOrder()->limit(rand(1, 2))->pluck('id')->toArray()
                );
                $job->categories()->attach(
                    Category::inRandomOrder()->limit(rand(1, 2))->pluck('id')->toArray()
                );

                // Add random attribute values in bulk
                $jobAttributes = [];
                foreach ($attributes as $attr) {
                    // Decode options if stored as JSON string
                    $options = is_string($attr->options) ? json_decode($attr->options, true) : $attr->options;

                    $jobAttributes[] = [
                        'job_id'       => $job->id,
                        'attribute_id' => $attr->id,
                        'value'        => $attr->type === 'number' ? rand(1, 10) :
                                          ($attr->type === 'boolean' ? $faker->boolean :
                                          ($attr->type === 'select' && is_array($options) ? $faker->randomElement($options) :
                                          $faker->word)),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
                
                // Insert job attributes in bulk
                JobAttributeValue::insert($jobAttributes);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
