<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = ['city', 'state', 'country'];

    /**
     * Define a many-to-many relationship with the Job model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function jobs()
    {
        return $this->belongsToMany(Job::class);
    }
}
