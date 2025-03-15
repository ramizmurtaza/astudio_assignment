<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'title', 
        'description', 
        'company_name', 
        'salary_min', 
        'salary_max', 
        'is_remote', 
        'job_type', 
        'status', 
        'published_at'
    ];

    /**
     * Attribute casting for automatic type conversion.
     *
     * @var array
     */
    protected $casts = [
        'is_remote' => 'boolean', // Converts is_remote to a boolean
        'published_at' => 'datetime', // Converts published_at to a DateTime instance
    ];

    /**
     * Define a many-to-many relationship with the Language model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function languages()
    {
        return $this->belongsToMany(Language::class);
    }

    /**
     * Define a many-to-many relationship with the Location model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    /**
     * Define a many-to-many relationship with the Category model.
     * Uses the pivot table 'job_category' for linking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'job_category');
    }

    /**
     * Define a one-to-many relationship with the JobAttributeValue model.
     * This follows the Entity-Attribute-Value (EAV) pattern.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobAttributes()
    {
        return $this->hasMany(JobAttributeValue::class);
    }
}
