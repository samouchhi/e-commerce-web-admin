<?php

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->artisan('migrate', ['--path' => [
        'database/migrations/2026_08_20_034201_create_products_table.php',
        'database/migrations/2026_08_20_082020_add_is_active_to_products_table.php',
        'database/migrations/2026_08_21_040808_create_product_variants_table.php',
        'database/migrations/2026_08_24_040228_rename_sku_from_product_variants_table.php',
        'database/migrations/2026_08_24_044021_add_product_code_to_products_table.php',
        'database/migrations/2026_08_24_044053_remove_product_code_from_product_variants_table.php',
        'database/migrations/2026_08_24_073625_create_product_images_table.php',
        'database/migrations/2026_09_03_083128_create_customers_table.php',
        'database/migrations/2026_09_17_114120_create_discounts_table.php',
        'database/migrations/2026_09_17_115402_create_discount_product_table.php',
        'database/migrations/2026_10_27_191858_create_wishlist_tables.php',
    ]])->assertSuccessful();
});

test('customers save whole products once and read them in newest save order', function () {
    $customer = Customer::withoutEvents(fn () => Customer::create(['name' => 'A', 'email' => 'a@example.test']));
    $product = Product::withoutEvents(fn () => Product::create(['name' => 'Nest', 'product_code' => 'BN', 'is_active' => true]));
    Sanctum::actingAs($customer);
    $operation = ['id' => (string) Str::uuid(), 'action' => 'save', 'product' => ['id' => $product->id, 'name' => 'Untrusted label', 'images' => []]];
    $this->postJson('/api/wishlist/operations', ['operations' => [$operation]])->assertSuccessful();
    $this->getJson('/api/wishlist')->assertSuccessful()->assertJsonCount(1, 'entries')->assertJsonPath('entries.0.id', $product->id)->assertJsonPath('entries.0.name', 'Nest');
});

function wishlistCustomer(string $name): Customer
{
    return Customer::withoutEvents(fn () => Customer::create(['name' => $name, 'email' => $name.'@example.test']));
}

function wishlistProduct(string $name): Product
{
    return Product::withoutEvents(fn () => Product::create(['name' => $name, 'is_active' => true]));
}

function wishlistOperation(int $id, string $action = 'save'): array
{
    return ['id' => (string) Str::uuid(), 'action' => $action, 'product' => ['id' => $id, 'name' => 'Snapshot', 'images' => []]];
}

test('wishlist APIs require a customer and isolate each account', function () {
    $this->getJson('/api/wishlist')->assertUnauthorized();
    $this->postJson('/api/wishlist/operations', ['operations' => []])->assertUnauthorized();
    $a = wishlistCustomer('a');
    $b = wishlistCustomer('b');
    $product = wishlistProduct('Nest');
    Sanctum::actingAs($a);
    $this->postJson('/api/wishlist/operations', ['operations' => [wishlistOperation($product->id)]])->assertSuccessful();
    Sanctum::actingAs($b);
    $this->getJson('/api/wishlist')->assertJsonCount(0, 'entries');
    $this->postJson('/api/wishlist/operations', ['operations' => [wishlistOperation($product->id, 'remove')], 'customer_id' => $a->id])->assertSuccessful();
    Sanctum::actingAs($a);
    $this->getJson('/api/wishlist')->assertJsonCount(1, 'entries');
});

test('duplicate saves preserve order and old operation retries never undo newer actions', function () {
    Sanctum::actingAs(wishlistCustomer('a'));
    $one = wishlistProduct('First');
    $two = wishlistProduct('Second');
    $save = wishlistOperation($one->id);
    $this->postJson('/api/wishlist/operations', ['operations' => [$save, wishlistOperation($two->id), wishlistOperation($one->id)]])->assertSuccessful()->assertJsonPath('entries.0.id', $two->id);
    $remove = wishlistOperation($one->id, 'remove');
    $this->postJson('/api/wishlist/operations', ['operations' => [$remove]])->assertSuccessful();
    $this->postJson('/api/wishlist/operations', ['operations' => [$save]])->assertSuccessful()->assertJsonCount(1, 'entries');
    $this->postJson('/api/wishlist/operations', ['operations' => [wishlistOperation($one->id)]])->assertSuccessful()->assertJsonPath('entries.0.id', $one->id);
    $this->postJson('/api/wishlist/operations', ['operations' => [$remove]])->assertSuccessful()->assertJsonCount(2, 'entries')->assertJsonStructure(['revision']);
});

test('merges keep existing order and retries cannot resurrect a later removal', function () {
    Sanctum::actingAs(wishlistCustomer('a'));
    $one = wishlistProduct('First');
    $two = wishlistProduct('Second');
    $this->postJson('/api/wishlist/operations', ['operations' => [wishlistOperation($one->id), wishlistOperation($two->id)]])->assertSuccessful();
    $merge = wishlistOperation($one->id, 'merge');
    $this->postJson('/api/wishlist/operations', ['operations' => [$merge]])->assertSuccessful()->assertJsonPath('entries.0.id', $two->id)->assertJsonCount(2, 'entries');
    $this->postJson('/api/wishlist/operations', ['operations' => [wishlistOperation($one->id, 'remove')]])->assertSuccessful();
    $this->postJson('/api/wishlist/operations', ['operations' => [$merge]])->assertSuccessful()->assertJsonCount(1, 'entries');
    $this->postJson('/api/wishlist/operations', ['operations' => [wishlistOperation($one->id)]])->assertSuccessful()->assertJsonPath('entries.0.id', $one->id);
});

test('availability resolution distinguishes hidden missing and sold out products without pagination', function () {
    $product = wishlistProduct('Nest');
    $hidden = wishlistProduct('Hidden');
    Product::withoutEvents(fn () => $hidden->update(['is_active' => false]));
    $this->postJson('/api/wishlist/resolve', ['ids' => [$product->id, $hidden->id, 999]])->assertSuccessful()
        ->assertJsonPath('products.0.product.name', 'Nest')->assertJsonPath('products.1.product', null)->assertJsonPath('products.2.product', null);
    Sanctum::actingAs(wishlistCustomer('a'));
    $this->postJson('/api/wishlist/operations', ['operations' => [wishlistOperation($product->id)]])->assertSuccessful();
    Product::withoutEvents(fn () => $product->update(['name' => 'Updated nest']));
    $this->getJson('/api/wishlist')->assertJsonPath('entries.0.name', 'Updated nest')->assertJsonPath('entries.0.available', true);
    Product::withoutEvents(fn () => $product->update(['is_active' => false]));
    $this->getJson('/api/wishlist')->assertJsonPath('entries.0.name', 'Updated nest')->assertJsonPath('entries.0.available', false)->assertJsonPath('entries.0.product', null);
    Product::withoutEvents(fn () => $product->update(['is_active' => true]));
    $this->getJson('/api/wishlist')->assertJsonPath('entries.0.available', true);
    Product::withoutEvents(fn () => $product->delete());
    $this->getJson('/api/wishlist')->assertJsonCount(1, 'entries')->assertJsonPath('entries.0.name', 'Updated nest')->assertJsonPath('entries.0.product', null);
    $this->postJson('/api/wishlist/operations', ['operations' => [wishlistOperation($product->id, 'remove')]])->assertSuccessful()->assertJsonCount(0, 'entries');
});

test('bad requests and conflicting operation identifiers leave the wishlist unchanged', function () {
    Sanctum::actingAs(wishlistCustomer('a'));
    $product = wishlistProduct('Nest');
    $operation = wishlistOperation($product->id);
    $this->postJson('/api/wishlist/operations', ['operations' => [$operation]])->assertSuccessful();
    $changed = [...$operation, 'action' => 'remove'];
    $this->postJson('/api/wishlist/operations', ['operations' => [$changed]])->assertConflict();
    $this->postJson('/api/wishlist/operations', ['operations' => [wishlistOperation($product->id, 'remove'), $changed]])->assertConflict();
    $this->postJson('/api/wishlist/operations', ['operations' => [['id' => 'bad', 'action' => 'toggle', 'product' => ['id' => -1]]]])->assertUnprocessable();
    $this->postJson('/api/wishlist/operations', ['operations' => [[...wishlistOperation(999), 'product' => ['id' => 999, 'name' => 'X', 'images' => [['image_url' => 'javascript:alert(1)']]]]]])->assertUnprocessable();
    $this->postJson('/api/wishlist/resolve', ['ids' => [1, 1]])->assertUnprocessable();
    $this->getJson('/api/wishlist')->assertJsonCount(1, 'entries');
});

test('sold out products expose current prices and primary image snapshots survive deletion', function () {
    Sanctum::actingAs(wishlistCustomer('a'));
    $product = wishlistProduct('Nest');
    Product::withoutEvents(fn () => $product->images()->createMany([
        ['image_path' => 'products/other.jpg', 'sort_order' => 5],
        ['image_path' => 'products/primary.jpg', 'sort_order' => 0],
    ]));
    $variant = Product::withoutEvents(fn () => $product->variants()->create(['name' => '100g', 'price' => 25, 'cost' => 10, 'stock_qty' => 0, 'is_active' => true]));
    $this->postJson('/api/wishlist/operations', ['operations' => [wishlistOperation($product->id)]])->assertSuccessful()
        ->assertJsonPath('entries.0.available', true)->assertJsonPath('entries.0.product.variants.0.stock_qty', 0)
        ->assertJsonPath('entries.0.images.0.image_path', 'products/primary.jpg');
    Product::withoutEvents(fn () => $variant->update(['price' => 30]));
    $this->postJson('/api/wishlist/resolve', ['ids' => [$product->id]])->assertSuccessful()->assertJsonPath('products.0.product.variants.0.price', fn (int|float|string $price): bool => (float) $price === 30.0);
    Product::withoutEvents(fn () => $product->delete());
    $this->getJson('/api/wishlist')->assertSuccessful()->assertJsonPath('entries.0.images.0.image_path', 'products/primary.jpg')->assertJsonPath('entries.0.product', null);
});
