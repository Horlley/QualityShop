<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LabScenarioTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_controlled_cart_defect_can_be_activated_and_deactivated(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 60, 'stock' => 10]);

        $this->actingAs($user)->post(route('lab.cart-scenario.update'), ['active' => true])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('lab_cart_total_stale', true);

        $this->actingAs($user)->withSession([
            'cart' => [$product->id => 2],
            'lab_cart_total_stale' => true,
        ])->get(route('cart.index'))
            ->assertOk()
            ->assertSeeText('CENÁRIO CONTROLADO QS-BUG-01')
            ->assertDontSeeText('R$ 120,00');

        $this->actingAs($user)->post(route('lab.cart-scenario.update'), ['active' => false])
            ->assertSessionHas('lab_cart_total_stale', false);
    }
}
