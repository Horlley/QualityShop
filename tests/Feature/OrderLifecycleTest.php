<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_replayed_checkout_does_not_duplicate_order_payment_or_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 60, 'stock' => 10]);
        $key = (string) Str::uuid();
        $this->actingAs($user)->withSession(['cart' => [$product->id => 2], 'checkout_key' => $key])
            ->post(route('checkout.store'), ['checkout_key' => $key])->assertRedirect();

        $this->post(route('checkout.store'), ['checkout_key' => $key])->assertRedirect(route('orders.show', Order::query()->sole()));

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertSame(8, $product->refresh()->stock);
        $this->assertSame(1, Order::query()->sole()->events()->where('event', 'approve')->count());
    }

    public function test_declined_payment_can_be_retried_without_reserving_stock_again(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 60, 'stock' => 10]);
        $key = (string) Str::uuid();
        $this->actingAs($user)->withSession(['cart' => [$product->id => 2], 'checkout_key' => $key])
            ->post(route('checkout.store'), ['checkout_key' => $key, 'payment_result' => 'declined'])->assertRedirect();
        $order = Order::query()->sole();
        $this->assertSame('created', $order->status);
        $this->assertSame('declined', $order->payment_status);

        $this->post(route('orders.transition', $order), ['action' => 'approve'])->assertRedirect();
        $this->post(route('orders.transition', $order), ['action' => 'approve'])->assertRedirect();

        $this->assertSame('paid', $order->refresh()->status);
        $this->assertSame(8, $product->refresh()->stock);
        $this->assertSame(1, $order->events()->where('event', 'approve')->count());
    }

    public function test_refund_failure_preserves_order_and_stock_then_success_records_single_refund(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 8]);
        $order = Order::factory()->for($user)->create();
        $order->items()->create(['product_id' => $product->id, 'sku' => $product->sku, 'name' => $product->name, 'unit_price' => 60, 'quantity' => 2, 'line_total' => 120]);

        $this->actingAs($user)->post(route('orders.cancellation.store', $order), ['simulate_refund_failure' => 1])->assertSessionHasErrors('order');

        $this->assertSame('paid', $order->refresh()->status);
        $this->assertSame(8, $product->refresh()->stock);
        $this->assertDatabaseCount('order_events', 0);

        $this->post(route('orders.cancellation.store', $order))->assertRedirect();
        $this->assertSame('refunded', $order->refresh()->payment_status);
        $this->assertSame(10, $product->refresh()->stock);
        $this->assertSame(1, $order->events()->where('event', 'refunded')->count());
    }

    public function test_customer_cannot_ship_order_and_invalid_transition_has_no_side_effect(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $this->actingAs($user)->post(route('orders.transition', $order), ['action' => 'ship'])->assertForbidden();
        $this->actingAs(User::factory()->operator()->create())->post(route('orders.transition', $order), ['action' => 'deliver'])->assertSessionHasErrors('order');

        $this->assertSame('paid', $order->refresh()->status);
        $this->assertDatabaseCount('order_events', 0);
    }

    public function test_staff_can_process_ship_and_deliver_but_shipped_order_cannot_be_cancelled(): void
    {
        $order = Order::factory()->create();
        $operator = User::factory()->operator()->create();

        $this->actingAs($operator)->post(route('orders.transition', $order), ['action' => 'process'])->assertRedirect();
        $this->assertSame('processing', $order->refresh()->status);
        $this->post(route('orders.transition', $order), ['action' => 'ship'])->assertRedirect();
        $this->post(route('orders.cancellation.store', $order))->assertSessionHasErrors('order');
        $this->assertSame('shipped', $order->refresh()->status);
        $this->post(route('orders.transition', $order), ['action' => 'deliver'])->assertRedirect();

        $this->assertSame('delivered', $order->refresh()->status);
        $this->assertDatabaseCount('order_events', 3);
    }

    public function test_another_customer_cannot_pay_or_cancel_order(): void
    {
        $order = Order::factory()->create(['status' => 'created', 'paid_at' => null, 'payment_status' => 'pending']);

        $this->actingAs(User::factory()->create())->post(route('orders.transition', $order), ['action' => 'approve'])->assertNotFound();
        $this->post(route('orders.cancellation.store', $order))->assertNotFound();

        $this->assertSame('created', $order->refresh()->status);
        $this->assertDatabaseCount('order_events', 0);
    }

    public function test_checkout_revalidates_stock_and_rolls_back_all_items(): void
    {
        $user = User::factory()->create();
        $available = Product::factory()->create(['stock' => 10]);
        $unavailable = Product::factory()->create(['stock' => 0]);
        $key = (string) Str::uuid();

        $this->actingAs($user)->withSession(['cart' => [$available->id => 2, $unavailable->id => 1], 'checkout_key' => $key])
            ->post(route('checkout.store'), ['checkout_key' => $key])->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(10, $available->refresh()->stock);
    }

    public function test_operator_can_create_pending_order_for_customer_and_customer_cannot_impersonate(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $key = (string) Str::uuid();
        $this->actingAs(User::factory()->operator()->create())->withSession(['cart' => [$product->id => 1], 'checkout_key' => $key])
            ->post(route('checkout.store'), ['checkout_key' => $key, 'customer_id' => $customer->id, 'payment_result' => 'pending'])->assertRedirect();

        $this->assertDatabaseHas('orders', ['user_id' => $customer->id, 'status' => 'created', 'payment_status' => 'pending']);
        $this->actingAs(User::factory()->create())->post(route('checkout.store'), ['checkout_key' => (string) Str::uuid(), 'customer_id' => $customer->id])->assertForbidden();
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_timeout_keeps_payment_pending_and_cancellation_does_not_issue_refund(): void
    {
        $order = Order::factory()->create(['status' => 'created', 'paid_at' => null, 'payment_status' => 'pending']);

        $this->actingAs($order->user)->post(route('orders.transition', $order), ['action' => 'timeout'])->assertRedirect();
        $this->assertSame('pending', $order->refresh()->payment_status);
        $this->post(route('orders.cancellation.store', $order))->assertRedirect();

        $this->assertSame('cancelled', $order->refresh()->payment_status);
        $this->assertSame(0, $order->events()->where('event', 'refunded')->count());
    }

    public function test_checkout_ignores_forged_price_and_applies_rounding_in_cents(): void
    {
        $product = Product::factory()->create(['price' => 100.05, 'stock' => 10]);
        $key = (string) Str::uuid();

        $this->actingAs(User::factory()->create())->withSession(['cart' => [$product->id => 1], 'checkout_key' => $key, 'coupon_code' => 'QA10'])
            ->post(route('checkout.store'), ['checkout_key' => $key, 'total' => 1, 'discount' => 99, 'unit_price' => 1])->assertRedirect();

        $order = Order::query()->sole();
        $this->assertSame('100.05', $order->subtotal);
        $this->assertSame('10.01', $order->discount);
        $this->assertSame('90.04', $order->total);
    }

    public function test_missing_checkout_key_is_rejected_without_creating_order(): void
    {
        $this->actingAs(User::factory()->create())->post(route('checkout.store'))->assertSessionHasErrors('checkout_key');

        $this->assertDatabaseCount('orders', 0);
    }
}
