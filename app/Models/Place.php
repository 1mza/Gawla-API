<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    protected $table = "places";
    protected $fillable = ['name', 'images', 'location', 'description', 'category', 'physical_disability_accessible', 'rate'];
    protected $casts = [
        'images' => 'json', // Cast 'images' attribute to JSON
    ];
}
