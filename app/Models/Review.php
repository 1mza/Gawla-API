<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['place_id','hotel_id','tourism_company_id', 'user_id', 'comment','rating'];

    // Define the relationship with the Hotel model
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    public function tourismCompany()
    {
        return $this->belongsTo(TourismCompany::class);
    }
    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
