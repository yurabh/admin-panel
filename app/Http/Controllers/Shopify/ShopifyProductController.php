<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Models\ShopifyProduct;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

class ShopifyProductController extends Controller
{
    #[OAT\Get(
        path: '/api/admin/shopify/products',
        description: 'Returns a paginated list of Shopify products synced into the local database. Supports filtering by title.',
        summary: 'List synced Shopify products',
        tags: ['Shopify'],
        parameters: [
            new OAT\Parameter(
                name: 'search',
                description: 'Filter products by title (partial match)',
                in: 'query',
                required: false,
                schema: new OAT\Schema(type: 'string')
            ),
            new OAT\Parameter(
                name: 'per_page',
                description: 'Number of items per page',
                in: 'query',
                required: false,
                schema: new OAT\Schema(type: 'integer', default: 15)
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(
                            property: 'data',
                            type: 'array',
                            items: new OAT\Items(
                                type: 'object',
                                example: [
                                    'id' => 1,
                                    'shopify_id' => 15832713363742,
                                    'title' => 'Test Product 1',
                                    'vendor' => 'My Store',
                                    'price' => '100.00',
                                    'inventory_quantity' => -1,
                                ]
                            )
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request)
    {
        $products = ShopifyProduct::query()
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderByDesc('shopify_created_at')
            ->paginate($request->per_page ?? 15);

        return response()->json($products);
    }

    #[OAT\Get(
        path: '/api/admin/shopify/products/{shopifyProduct}',
        description: 'Returns a single synced Shopify product by its local database ID.',
        summary: 'Get a single Shopify product',
        tags: ['Shopify'],
        parameters: [
            new OAT\Parameter(
                name: 'shopifyProduct',
                description: 'Local database ID of the product',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation',
                content: new OAT\JsonContent(
                    type: 'object',
                    example: [
                        'id' => 1,
                        'shopify_id' => 15832713363742,
                        'title' => 'Test Product 1',
                        'vendor' => 'My Store',
                        'price' => '100.00',
                        'inventory_quantity' => -1,
                    ]
                )
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
            new OAT\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function show(ShopifyProduct $shopifyProduct)
    {
        return response()->json($shopifyProduct);
    }
}
