<?php

namespace App\Services\Shopify;

use App\Models\ShopifyOrder;
use Illuminate\Support\Carbon;

class OrderSyncService
{
    public function __construct(private ShopifyClient $client)
    {
    }

    public function sync(?string $since = null): int
    {
        $syncedCount = 0;
        $pageInfo = null;

        do {
            $query = ['limit' => 50, 'status' => 'any'];

            if ($pageInfo) {
                $query['page_info'] = $pageInfo;
            } elseif ($since) {
                $query['created_at_min'] = $since;
            }

            $response = $this->client->get('orders.json', $query);
            $orders = $response->json('orders') ?? [];

            foreach ($orders as $order) {
                $this->storeOrder($order);
                $syncedCount++;
            }

            $pageInfo = $this->extractNextPageInfo($response->header('Link'));

        } while ($pageInfo !== null);

        return $syncedCount;
    }

    private function storeOrder(array $order): void
    {
        $shippingAddress = $order['shipping_address'] ?? [];

        ShopifyOrder::updateOrCreate(
            ['shopify_id' => $order['id']],
            [
                'order_number' => $order['name'] ?? null,
                'email' => $order['email'] ?? null,
                'financial_status' => $order['financial_status'] ?? null,
                'fulfillment_status' => $order['fulfillment_status'] ?? null,
                'total_price' => $order['total_price'] ?? null,
                'currency' => $order['currency'] ?? null,
                'customer_first_name' => $order['customer']['first_name'] ?? null,
                'customer_last_name' => $order['customer']['last_name'] ?? null,
                'shipping_country' => $shippingAddress['country'] ?? null,
                'shipping_city' => $shippingAddress['city'] ?? null,
                'line_items' => $order['line_items'] ?? [],
                'raw_data' => $order,
                'shopify_created_at' => Carbon::parse($order['created_at']),
            ]
        );
    }

    private function extractNextPageInfo(?string $linkHeader): ?string
    {
        if (!$linkHeader) {
            return null;
        }

        if (preg_match('/<[^>]*page_info=([^&>]+)[^>]*>;\s*rel="next"/', $linkHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
