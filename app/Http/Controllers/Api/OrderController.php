<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Logistic;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\OrderStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
            $variants = ProductVariant::query()
                ->whereIn('id', array_column($items, 'product_variant_id'))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $subtotalCents = 0;

            foreach ($items as $item) {
                $variant = $variants->get($item['product_variant_id']);
                abort_unless($variant !== null, 422, 'A selected product variant is no longer available.');
                $subtotalCents += (int) round((float) $variant->price * 100) * $item['quantity'];
            }

            $shippingCents = isset($data['logistic_id'])
                ? (int) round((float) Logistic::findOrFail($data['logistic_id'])->price * 100)
                : 0;
            $data['subtotal_amount'] = number_format($subtotalCents / 100, 2, '.', '');
            $data['shipping_cost'] = number_format($shippingCents / 100, 2, '.', '');
            $data['total_amount'] = number_format(($subtotalCents + $shippingCents) / 100, 2, '.', '');
            abort_unless($subtotalCents + $shippingCents >= 1 && $subtotalCents + $shippingCents <= 10000000, 422, 'Order total must be between 0.01 and 100000 USD.');

            $order = Order::create([
                ...Arr::except($data, [
                    'items',
                    'name',
                    'phone',
                    'address',
                    'city',
                    'note',
                ]),
                'customer_id' => $request->user()->id,
                'order_number' => 'ORD-'.random_int(100000, 999999),
                'payment_status' => PaymentStatus::Pending,
                'shipping_status' => ShippingStatus::Pending,

            ]);

            foreach ($items as $item) {
                $variant = $variants->get($item['product_variant_id']);
                $unitPrice = $variant->price;

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],

                    'unit_price' => $unitPrice,
                    'subtotal_price' => $unitPrice * $item['quantity'],
                ]);
            }
            $order->customer()->update([
                'name' => $data['name'],
                'phone' => $data['phone'],
            ]);
            $order->address()->create([
                'address' => $data['address'],
                'city' => $data['city'],
                'phone' => $data['phone'],
                'note' => $data['note'] ?? null,
            ]);
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
