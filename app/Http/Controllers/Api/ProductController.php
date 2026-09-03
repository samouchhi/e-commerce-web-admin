<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category', 'variants', 'images', 'unit'])->orderBy('created_at', 'desc')->paginate(10);

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $variants = $data['variants'] ?? [];
        $images = collect($request->file('images', []));

        $imageRecords = $images->map(fn (array $image, int $index): array => [
            'image_path' => $image['file']->store('product-images', 'public'),
            'sort_order' => $data['images'][$index]['sort_order'] ?? $index,
        ]);

        $product = DB::transaction(function () use ($data, $variants, $imageRecords): Product {
            $product = Product::create(Arr::except($data, ['variants', 'images']));
            $product->variants()->createMany($variants);
            $product->images()->createMany($imageRecords);

            return $product;
        });

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => new ProductResource(
                $product->load(['category', 'variants', 'images', 'unit'])
            ),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['category', 'variants', 'images', 'unit'])->findOrFail($id);

        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $variants = $data['variants'] ?? [];
        $images = $request->file('images', []);

        $imagePaths = collect($images)->map(
            fn ($image) => $image->store('product-images', 'public')
        );

        $product = DB::transaction(function () use ($data, $variants, $imagePaths, $product): Product {
            $product->update(Arr::except($data, ['variants', 'images']));
            $product->variants()->createMany($variants);

            $product->images()->createMany(
                $imagePaths->map(fn ($path, $i) => ['image_path' => $path, 'sort_order' => $i])
            );

            return $product;
        });

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => new ProductResource(
                $product->load(['category', 'variants', 'images', 'unit'])
            ),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function destroyVariant(Product $product, ProductVariant $variant)
    {
        $variant->delete();

        return response()->json([
            'message' => 'Product variant deleted successfully.',
        ]);
    }
}
