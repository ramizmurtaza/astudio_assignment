<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'options'];

    // Cast attributes to appropriate data types
    protected $casts = [
        'options' => 'array', // Ensures 'options' is stored as an array
    ];

    /**
     * Define a one-to-many relationship with JobAttributeValue
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobValues()
    {
     
        return $this->hasMany(JobAttributeValue::class);
    }
}
