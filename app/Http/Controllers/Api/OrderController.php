<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\OrderStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::latest()->get();

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request, OrderStockService $stockService)
    {
        $data = $request->validated();
        $items = $data['items'];

        $order = DB::transaction(function () use ($data, $items, $request, $stockService): Order {
            $order = Order::create([
                ...Arr::except($data, ['items']),
                'customer_id' => $request->user()->id,
                'order_number' => 'ORD-' . random_int(100000, 999999),
                'payment_status' => PaymentStatus::Pending,
                'shipping_status' => ShippingStatus::Pending,
            ]);

            foreach ($items as $item) {
                $variant = ProductVariant::findOrFail($item['product_variant_id']);
                $unitPrice = $variant->price;

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],

                    'unit_price' => $unitPrice,
                    'subtotal_price' => $unitPrice * $item['quantity'],
                ]);
            }
            $order->payment()->create([
                'order_id' => $order->id,
                'currency' => 'USD',
                'amount' => $order->total_amount,
                'md5_hash' => null,
                'qr_expiration_at' => now()->addMinutes(2),
                'qr_paid_at' => null,
            ]);

            $stockService->reserve($order->load('items'));

            return $order;
        });

        return new OrderResource($order->load('items'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
