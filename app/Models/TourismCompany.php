<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourismCompany extends Model
{
    use HasFactory;
    use Filterable;

    protected $fillable = [
        'name',
        'images',
        'location',
        'description',
        'rate',
        'offers',
        'comments',
        'phone',
    ];
    protected $casts = [
        'images' => 'json', // Cast 'images' attribute to JSON
    ];
}

