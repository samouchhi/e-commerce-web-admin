<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            // Saved entries must survive deletion of their catalogue product.
            $table->unsignedBigInteger('product_id');
            $table->json('snapshot');
            $table->unique(['customer_id', 'product_id']);
            $table->index(['customer_id', 'id']);
        });
        Schema::create('wishlist_operations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->uuid('operation_id');
            $table->string('fingerprint', 64);
            $table->unique(['customer_id', 'operation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_operations');
        Schema::dropIfExists('wishlist_entries');
    }
};
