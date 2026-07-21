<?php

namespace App\Services\Shopify;

use App\Models\ShopifyProduct;
use Illuminate\Support\Carbon;

class ProductSyncService
{
    public function __construct(private ShopifyClient $client)
    {
    }

    public function sync(): int
    {
        $syncedCount = 0;
        $pageInfo = null;

        do {
            $query = ['limit' => 50];
            if ($pageInfo) {
                $query['page_info'] = $pageInfo;
            }

            $response = $this->client->get('products.json', $query);
            $products = $response->json('products') ?? [];

            foreach ($products as $product) {
                $this->storeProduct($product);
                $syncedCount++;
            }

            $pageInfo = $this->extractNextPageInfo($response->header('Link'));

        } while ($pageInfo !== null);

        return $syncedCount;
    }

    private function storeProduct(array $product): void
    {
        $firstVariant = $product['variants'][0] ?? null;
        $firstImage = $product['images'][0]['src'] ?? null;

        ShopifyProduct::updateOrCreate(
            ['shopify_id' => $product['id']],
            [
                'title' => $product['title'],
                'vendor' => $product['vendor'] ?? null,
                'product_type' => $product['product_type'] ?? null,
                'status' => $product['status'] ?? null,
                'image_url' => $firstImage,
                'price' => $firstVariant['price'] ?? null,
                'inventory_quantity' => $firstVariant['inventory_quantity'] ?? null,
                'raw_data' => $product,
                'shopify_created_at' => Carbon::parse($product['created_at']),
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
