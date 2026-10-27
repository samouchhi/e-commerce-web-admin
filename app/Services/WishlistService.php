<?php

namespace App\Services;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class WishlistService
{
    public function read(int $customerId): array
    {
        return DB::transaction(function () use ($customerId): array {
            DB::table('customers')->where('id', $customerId)->lockForUpdate()->first();

            return $this->entries($customerId);
        });
    }

    public function sync(int $customerId, array $operations): array
    {
        return DB::transaction(function () use ($customerId, $operations): array {
            DB::table('customers')->where('id', $customerId)->lockForUpdate()->first();
            $products = $this->products(array_column(array_column($operations, 'product'), 'id'));
            foreach ($operations as $operation) {
                $fingerprint = hash('sha256', json_encode($operation, JSON_THROW_ON_ERROR));
                $previous = DB::table('wishlist_operations')->where('customer_id', $customerId)->where('operation_id', $operation['id'])->first();
                if ($previous !== null) {
                    abort_unless(hash_equals($previous->fingerprint, $fingerprint), 409, 'Operation identifier was reused with different data.');

                    continue;
                }
                DB::table('wishlist_operations')->insert(['customer_id' => $customerId, 'operation_id' => $operation['id'], 'fingerprint' => $fingerprint]);
                $entry = DB::table('wishlist_entries')->where('customer_id', $customerId)->where('product_id', $operation['product']['id']);
                if ($operation['action'] === 'remove') {
                    $entry->delete();
                } elseif (! $entry->exists()) {
                    $current = $products[$operation['product']['id']] ?? null;
                    $snapshot = $current === null ? $operation['product'] : $this->snapshot($current);
                    DB::table('wishlist_entries')->insert([
                        'customer_id' => $customerId,
                        'product_id' => $operation['product']['id'],
                        'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                    ]);
                }
            }

            return $this->entries($customerId);
        }, 3);
    }

    public function resolve(array $ids): array
    {
        $products = $this->products($ids);

        return array_map(fn (int $id): array => ['id' => $id, 'product' => $products[$id] ?? null], $ids);
    }

    private function products(array $ids): array
    {
        return Product::query()->with(['variants', 'images', 'discounts'])->whereIn('id', $ids)->where('is_active', true)->get()
            ->mapWithKeys(fn (Product $product): array => [$product->id => (new ProductResource($product))->resolve()])->all();
    }

    private function snapshot(array $product): array
    {
        return ['id' => $product['id'], 'name' => $product['name'], 'product_code' => $product['product_code'], 'images' => collect($product['images'])->sortBy('sort_order')->take(1)->values()->all()];
    }

    private function entries(int $customerId): array
    {
        $entries = DB::table('wishlist_entries')->where('customer_id', $customerId)->orderByDesc('id')->get();
        $products = $this->products($entries->pluck('product_id')->all());

        return ['revision' => (int) DB::table('wishlist_operations')->where('customer_id', $customerId)->max('id'), 'entries' => $entries->map(function (object $entry) use ($products): array {
            $product = $products[$entry->product_id] ?? null;
            $snapshot = json_decode($entry->snapshot, true, flags: JSON_THROW_ON_ERROR);
            if ($product !== null) {
                $current = $this->snapshot($product);
                if ($current !== $snapshot) {
                    $snapshot = $current;
                    DB::table('wishlist_entries')->where('id', $entry->id)->update(['snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR)]);
                }
            }

            return [...$snapshot, 'product' => $product, 'available' => $product !== null];
        })->all()];
    }
}
