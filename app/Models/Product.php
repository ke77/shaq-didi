<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory; 
    
    protected $fillable = [
        'name',
        'restaurant_id',
        'description',
        'price',
        'is_available'
    ];


    // relationship: a product belongs to a restaurant
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
