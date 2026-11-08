<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class Order extends Model
{
    // this order belongs to this PS, this NSP and this product
    protected $fillable = [
        'product_id', 
        'personnel_id', 
        'product_name', 
        'product_price', 
        'amount', 
        'total_price', 
        'order_note', 
        'status'
    ];

    #[Override]
    protected function casts()
    {
        return [
            'status' => OrderStatus::class
        ];
    }

    // these three methods are used on the /api/orders endpoint to fetch orders for either PS or NSP, depending on the logged-in user's role
    // PS who placed order
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
