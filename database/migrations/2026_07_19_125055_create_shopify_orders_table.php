<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shopify_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopify_id')->unique();
            $table->string('order_number')->nullable();
            $table->string('email')->nullable();
            $table->string('financial_status')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_city')->nullable();
            $table->json('line_items')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('shopify_created_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_orders');
    }
};
