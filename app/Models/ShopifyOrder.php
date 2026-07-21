<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyOrder extends Model
{
    protected $fillable = [
        'shopify_id',
        'order_number',
        'email',
        'financial_status',
        'fulfillment_status',
        'total_price',
        'currency',
        'customer_first_name',
        'customer_last_name',
        'shipping_country',
        'shipping_city',
        'line_items',
        'raw_data',
        'shopify_created_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'line_items' => 'array',
        'shopify_created_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];
}
