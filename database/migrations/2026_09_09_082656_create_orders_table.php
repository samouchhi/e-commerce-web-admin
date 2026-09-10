<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('logistic_id')->nullable()->constrained('logistics')->onDelete('set null');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('subtotal_amount', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('shipping_status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
