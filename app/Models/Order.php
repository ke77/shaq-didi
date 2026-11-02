<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    // this order belongs to this PS, this NSP and this product
    protected $fillable = [
        'user_id', 
        'product_id', 
        'personnel_id', 
        'product_name', 
        'product_price', 
        'amount', 
        'total_price', 
        'order_note', 
        'status'
    ];

    // these three methods are used on the /api/orders endpoint to fetch orders for either PS or NSP, depending on the logged-in user's role
    // PS member who placed order
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    // NSP handling order
    public function personnel():BelongsTo
    {
        return $this->belongsTo(User::class, 'personnel_id');
    } 


    // product link
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
