<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StorefrontFlowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_catalog_lists_only_active_products_and_applies_filters(): void
    {
        Product::factory()->create(['name' => 'Fone Aurora', 'category' => 'Eletrônicos', 'active' => true]);
        Product::factory()->create(['name' => 'Caneca Nebulosa', 'category' => 'Casa', 'active' => true]);
        Product::factory()->inactive()->create(['name' => 'Produto Oculto', 'category' => 'Eletrônicos']);

        $this->get(route('catalog.index', ['category' => 'Eletrônicos']))
            ->assertOk()
            ->assertSeeText('Fone Aurora')
            ->assertDontSeeText('Caneca Nebulosa')
            ->assertDontSeeText('Produto Oculto');
    }

    public function test_catalog_escapes_product_content(): void
    {
        Product::factory()->create([
            'name' => '<script>alert("qa")</script>',
            'description' => '<img src=x onerror=alert(1)>',
            'active' => true,
        ]);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("qa")</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_guest_is_redirected_to_login_when_adding_to_cart(): void
    {
        $product = Product::factory()->create();

        $this->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_add_and_update_product_within_limits(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 60]);

        $this->actingAs($user)
            ->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart.'.$product->id, 1);

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->patch(route('cart.items.update', $product), ['quantity' => 2])
            ->assertSessionHas('cart.'.$product->id, 2);

        $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSeeText('R$ 120,00');
    }

    public function test_quantity_above_ten_is_rejected_with_visible_message(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 20]);

        $this->actingAs($user)->from(route('catalog.index'))
            ->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 11])
            ->assertRedirect(route('catalog.index'))
            ->assertSessionHasErrors(['quantity' => 'A quantidade máxima por produto é 10.']);
    }

    public function test_qa10_requires_one_hundred_reais_and_then_applies_ten_percent(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 60, 'stock' => 10]);

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->post(route('cart.coupon.store'), ['coupon_code' => 'QA10'])
            ->assertSessionHasErrors(['coupon_code' => 'O cupom QA10 exige subtotal mínimo de R$ 100,00.']);

        $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])
            ->post(route('cart.coupon.store'), ['coupon_code' => 'qa10'])
            ->assertSessionHas('coupon_code', 'QA10');

        $this->actingAs($user)->withSession(['cart' => [$product->id => 2], 'coupon_code' => 'QA10'])
            ->get(route('cart.index'))
            ->assertSeeText('R$ 108,00');
    }
}
