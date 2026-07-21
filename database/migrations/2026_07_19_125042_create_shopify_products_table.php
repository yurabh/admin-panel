<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shopify_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopify_id')->unique();
            $table->string('title');
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->string('status')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('inventory_quantity')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('shopify_created_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_products');
    }
};
