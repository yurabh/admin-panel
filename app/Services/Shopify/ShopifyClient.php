<?php

namespace App\Services\Shopify;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use RuntimeException;

class ShopifyClient
{
    private string $shopDomain;
    private string $accessToken;
    private string $apiVersion;

    public function __construct()
    {
        $this->shopDomain = config('shopify.shop_domain');
        $this->accessToken = config('shopify.access_token');
        $this->apiVersion = config('shopify.api_version');
    }

    private function baseUrl(): string
    {
        return "https://{$this->shopDomain}/admin/api/{$this->apiVersion}";
    }

    private function headers(): array
    {
        return [
            'X-Shopify-Access-Token' => $this->accessToken,
            'Content-Type' => 'application/json',
        ];
    }

    public function get(string $endpoint, array $query = []): Response
    {
        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl()}/{$endpoint}", $query);

        if ($response->failed()) {
            logger()->error('Shopify API error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException(
                "Shopify API request failed: {$response->status()} - {$response->body()}"
            );
        }

        return $response;
    }
}
