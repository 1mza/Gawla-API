<?php
namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Abbasudo\Purity\Traits\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;
    use Filterable;
    use Sortable;
    protected $fillable = [
        'name',
        'images', // Change 'image' to 'image_url'
        'location',
        'description',
        'rate',
        'wifi',
        'pool',
        'car_parking',
        'sustainable_travel_level',
        'disability_accommodation',
        'price'
    ];
    protected $casts = [
        'images' => 'json', // Cast 'images' attribute to JSON
    ];
}
