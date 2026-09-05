<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_checkout_creates_paid_order_applies_coupon_and_decrements_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['sku' => 'QS-900', 'price' => 60, 'stock' => 10]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2], 'coupon_code' => 'QA10', 'checkout_key' => 'a550e840-e29b-41d4-a716-446655440001'])
            ->post(route('checkout.store'), ['checkout_key' => 'a550e840-e29b-41d4-a716-446655440001'])
            ->assertRedirect();

        $order = Order::query()->sole();
        $this->assertSame('paid', $order->status);
        $this->assertSame('120.00', $order->subtotal);
        $this->assertSame('12.00', $order->discount);
        $this->assertSame('108.00', $order->total);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'line_total' => 120,
        ]);
        $this->assertSame(8, $product->refresh()->stock);
    }

    public function test_checkout_rejects_empty_cart(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['checkout_key' => 'a550e840-e29b-41d4-a716-446655440002'])
            ->post(route('checkout.store'), ['checkout_key' => 'a550e840-e29b-41d4-a716-446655440002'])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors(['cart' => 'O carrinho está vazio.']);
    }

    public function test_customer_cannot_discover_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $order = Order::factory()->for($owner)->create();

        $this->actingAs($otherCustomer)->get(route('orders.show', $order))->assertNotFound();
    }

    public function test_operator_can_view_customer_order(): void
    {
        $order = Order::factory()->create();
        $operator = User::factory()->operator()->create();

        $this->actingAs($operator)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSeeText($order->number);
    }

    public function test_cancellation_restores_stock_once_and_records_state(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 8]);
        $order = Order::factory()->for($user)->create();
        $order->items()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'unit_price' => 60,
            'quantity' => 2,
            'line_total' => 120,
        ]);

        $this->actingAs($user)->post(route('orders.cancellation.store', $order))
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame('cancelled', $order->refresh()->status);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame(10, $product->refresh()->stock);

        $this->actingAs($user)->post(route('orders.cancellation.store', $order))
            ->assertSessionHasErrors(['order' => 'Este pedido não pode ser cancelado no estado atual.']);

        $this->assertSame(10, $product->refresh()->stock);
    }
}
