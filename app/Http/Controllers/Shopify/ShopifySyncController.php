<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Services\Shopify\OrderSyncService;
use App\Services\Shopify\ProductSyncService;
use OpenApi\Attributes as OAT;

class ShopifySyncController extends Controller
{
    #[OAT\Post(
        path: '/api/admin/shopify/sync',
        description: 'Synchronizes products and orders from Shopify into the local database. Fetches all products and orders from the last 3 months.',
        summary: 'Sync Shopify products and orders',
        tags: ['Shopify'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful synchronization',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'products_synced', type: 'integer', example: 3),
                        new OAT\Property(property: 'orders_synced', type: 'integer', example: 1),
                    ],
                    type: 'object',
                    example: [
                        'products_synced' => 3,
                        'orders_synced' => 1,
                    ]
                )
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
            new OAT\Response(response: 500, description: 'Shopify API request failed'),
        ]
    )]
    public function __invoke(ProductSyncService $productSync, OrderSyncService $orderSync)
    {
        $productsCount = $productSync->sync();
        $ordersCount = $orderSync->sync(now()->subMonths(3)->toDateString());

        return response()->json([
            'products_synced' => $productsCount,
            'orders_synced' => $ordersCount,
        ]);
    }
}
