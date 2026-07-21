<?php

namespace App\Console\Commands\Shopify;

use App\Services\Shopify\OrderSyncService;
use App\Services\Shopify\ProductSyncService;
use Illuminate\Console\Command;

class SyncShopifyData extends Command
{
    protected $signature = 'shopify:sync {--since= : Sync orders since this date (Y-m-d)}';

    protected $description = 'Sync products and orders from Shopify';

    public function handle(ProductSyncService $productSync, OrderSyncService $orderSync): int
    {
        $this->info('Syncing products...');
        $productsCount = $productSync->sync();

        $this->info("Synced {$productsCount} products.");

        $this->info('Syncing orders...');
        $since = $this->option('since') ?? now()->subMonths(3)->toDateString();

        $ordersCount = $orderSync->sync($since);
        $this->info("Synced {$ordersCount} orders.");

        return self::SUCCESS;
    }
}
