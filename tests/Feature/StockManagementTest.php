<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_manager_can_adjust_stock_but_not_price(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'price' => 60]);

        $this->actingAs(User::factory()->manager()->create())->patch(route('stock.update', $product), ['stock' => 20, 'previous_stock' => 10, 'price' => 1])->assertRedirect(route('stock.index'));

        $this->assertSame(20, $product->refresh()->stock);
        $this->assertSame('60.00', $product->price);
    }

    public function test_stale_or_negative_stock_adjustments_do_not_change_balance(): void
    {
        $product = Product::factory()->create(['stock' => 8]);

        $this->actingAs(User::factory()->manager()->create())->patch(route('stock.update', $product), ['stock' => 20, 'previous_stock' => 10])->assertSessionHasErrors('stock');
        $this->patch(route('stock.update', $product), ['stock' => -1, 'previous_stock' => 8])->assertSessionHasErrors('stock');

        $this->assertSame(8, $product->refresh()->stock);
    }

    public function test_operator_cannot_adjust_stock(): void
    {
        $product = Product::factory()->create(['stock' => 8]);

        $this->actingAs(User::factory()->operator()->create())->patch(route('stock.update', $product), ['stock' => 20, 'previous_stock' => 8])->assertForbidden();

        $this->assertSame(8, $product->refresh()->stock);
    }
}
