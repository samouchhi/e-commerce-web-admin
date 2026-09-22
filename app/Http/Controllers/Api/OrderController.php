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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auth = auth()->user();
        $orders = Order::query()
            ->where('customer_id', $auth->id)
            ->where('payment_status', PaymentStatus::Paid)
            ->with(['items', 'address', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request, OrderStockService $stockService): OrderResource
    {
        $data = $request->validated();
        $order = DB::transaction(function () use ($data, $request, $stockService): Order {
            $prepared = $this->prepareOrderItems($data['items']);
            $shippingCents = isset($data['logistic_id'])
                ? (int) round((float) Logistic::findOrFail($data['logistic_id'])->price * 100)
                : 0;
            $totalCents = $prepared['subtotal_cents'] + $shippingCents;

            if ($totalCents < 1 || $totalCents > 10000000) {
                abort(422, 'Order total must be between 0.01 and 100000 USD.');
            }

            $order = Order::create([
                'customer_id' => $request->user()->id,
                'logistic_id' => $data['logistic_id'] ?? null,
                'order_number' => 'ORD-'.Str::upper(Str::random(6)),
                'subtotal_amount' => number_format($prepared['subtotal_cents'] / 100, 2, '.', ''),
                'shipping_cost' => number_format($shippingCents / 100, 2, '.', ''),
                'total_amount' => number_format($totalCents / 100, 2, '.', ''),
                'payment_status' => PaymentStatus::Pending,
                'shipping_status' => ShippingStatus::Pending,
            ]);

            $order->items()->createMany($prepared['items']);
            $this->saveOrderDetails($order, $data);
            $stockService->reserve($order->load('items'));

            return $order;
        });

        return new OrderResource($order->loadMissing(['customer', 'logistic', 'items.productVariant.product']));
    }

    /**
     * @param  array<int, array{product_variant_id: int, quantity: int}>  $items
     * @return array{subtotal_cents: int, items: array<int, array{product_variant_id: int, quantity: int, unit_price: string, subtotal_price: string}>}
     */
    private function prepareOrderItems(array $items): array
    {
        $variants = ProductVariant::query()
            ->with('product.discounts')
            ->whereIn('id', array_column($items, 'product_variant_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
        $subtotalCents = 0;
        $orderItems = [];

        foreach ($items as $item) {
            $variant = $variants->get($item['product_variant_id']);

            if ($variant === null || ! $variant->is_active || ! $variant->product->is_active) {
                abort(422, 'A selected product variant is no longer available.');
            }

            $unitPriceCents = (int) round($variant->product->discountedPriceFor($variant->price) * 100);
            $itemTotalCents = $unitPriceCents * $item['quantity'];
            $subtotalCents += $itemTotalCents;
            $orderItems[] = [
                'product_variant_id' => $variant->id,
                'quantity' => $item['quantity'],
                'unit_price' => number_format($unitPriceCents / 100, 2, '.', ''),
                'subtotal_price' => number_format($itemTotalCents / 100, 2, '.', ''),
            ];
        }

        return ['subtotal_cents' => $subtotalCents, 'items' => $orderItems];
    }

    /**
     * @param  array{name: string, phone: string, address: string, city: string, note?: string|null}  $data
     */
    private function saveOrderDetails(Order $order, array $data): void
    {
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
            'currency' => 'USD',
            'amount' => $order->total_amount,
            'qr_expiration_at' => now()->addMinutes(2),
        ]);
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
