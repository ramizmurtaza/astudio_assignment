<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attribute;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
    }
}
