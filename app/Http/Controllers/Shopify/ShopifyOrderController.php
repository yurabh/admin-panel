<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Models\ShopifyOrder;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

class ShopifyOrderController extends Controller
{
    #[OAT\Get(
        path: '/api/admin/shopify/orders',
        description: 'Returns a paginated list of Shopify orders synced into the local database. Supports filtering by financial status.',
        summary: 'List synced Shopify orders',
        tags: ['Shopify'],
        parameters: [
            new OAT\Parameter(
                name: 'status',
                description: 'Filter orders by financial status (e.g. paid, pending, refunded)',
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
                                    'shopify_id' => 6234567890,
                                    'order_number' => '#1001',
                                    'email' => 'customer@example.com',
                                    'financial_status' => 'paid',
                                    'total_price' => '120.00',
                                    'currency' => 'USD',
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
        $orders = ShopifyOrder::query()
            ->when($request->status, fn($q) => $q->where('financial_status', $request->status))
            ->orderByDesc('shopify_created_at')
            ->paginate($request->per_page ?? 15);

        return response()->json($orders);
    }

    #[OAT\Get(
        path: '/api/admin/shopify/orders/{shopifyOrder}',
        description: 'Returns a single synced Shopify order by its local database ID.',
        summary: 'Get a single Shopify order',
        tags: ['Shopify'],
        parameters: [
            new OAT\Parameter(
                name: 'shopifyOrder',
                description: 'Local database ID of the order',
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
                        'shopify_id' => 6234567890,
                        'order_number' => '#1001',
                        'email' => 'customer@example.com',
                        'financial_status' => 'paid',
                        'total_price' => '120.00',
                        'currency' => 'USD',
                    ]
                )
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
            new OAT\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function show(ShopifyOrder $shopifyOrder)
    {
        return response()->json($shopifyOrder);
    }
}
