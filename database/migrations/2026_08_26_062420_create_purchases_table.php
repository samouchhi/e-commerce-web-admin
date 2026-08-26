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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->date('purchase_date')->nullable();
            $table->date('received_date')->nullable();
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->enum('payment_status', ['pending', 'paid', 'partial'])->default('pending');
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->string('shipping_status', 20)->default('processing');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
