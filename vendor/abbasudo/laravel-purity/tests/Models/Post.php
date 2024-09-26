<?php

namespace Abbasudo\Purity\Tests\Models;

use Abbasudo\Purity\Traits\Filterable;
use Abbasudo\Purity\Traits\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;
    use Filterable;
    use Sortable;

    protected $fillable = [
        'title',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
