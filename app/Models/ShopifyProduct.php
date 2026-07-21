<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyProduct extends Model
{
    protected $fillable = [
        'shopify_id',
        'title',
        'vendor',
        'product_type',
        'status',
        'image_url',
        'price',
        'inventory_quantity',
        'raw_data',
        'shopify_created_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'shopify_created_at' => 'datetime',
        'price' => 'decimal:2',
    ];
}
