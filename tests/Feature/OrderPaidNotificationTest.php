<?php

use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Filament\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('migrate', ['--path' => [
        'database/migrations/0001_01_01_000000_create_users_table.php',
        'database/migrations/2026_08_20_083806_create_permission_tables.php',
        'database/migrations/2026_09_03_023456_create_logistics_table.php',
        'database/migrations/2026_09_03_083128_create_customers_table.php',
        'database/migrations/2026_09_09_082656_create_orders_table.php',
    ]])->assertSuccessful();

    Notification::fake();
    $this->staff = User::factory()->create();
    $this->staff->assignRole(Role::create(['name' => 'staff', 'guard_name' => 'web']));
    $this->nonStaff = User::factory()->create();
    $customer = Customer::create(['name' => 'Customer', 'email' => 'customer@example.test']);
    $this->order = Order::create([
        'order_number' => 'ORD-123456',
        'customer_id' => $customer->id,
        'total_amount' => 10,
        'subtotal_amount' => 10,
        'payment_status' => PaymentStatus::Pending,
    ]);
});

test('a paid order notifies dashboard staff only after commit', function () {
    Notification::assertNothingSent();
    DB::transaction(function () {
        $this->order->update(['payment_status' => PaymentStatus::Paid]);
        Notification::assertNothingSent();
    });

    Notification::assertSentTo($this->staff, DatabaseNotification::class, function (DatabaseNotification $notification) {
        return $notification->data['title'] === 'New paid order'
            && str_contains($notification->data['body'], $this->order->order_number);
    });
    Notification::assertNotSentTo($this->nonStaff, DatabaseNotification::class);

    $this->order->update(['total_amount' => 12]);
    $this->order->update(['payment_status' => PaymentStatus::Paid]);
    Notification::assertCount(1);
});

test('unpaid orders and rolled back payments do not notify staff', function () {
    $this->order->update(['payment_status' => PaymentStatus::Expired]);
    Notification::assertNothingSent();
    DB::beginTransaction();
    $this->order->update(['payment_status' => PaymentStatus::Paid]);
    DB::rollBack();

    Notification::assertNothingSent();
    expect($this->order->fresh()->payment_status)->toBe(PaymentStatus::Expired);
});
