<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Language extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = ['name'];

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
