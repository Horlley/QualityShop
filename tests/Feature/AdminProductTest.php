<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_customer_is_forbidden_from_product_administration(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_product_from_validated_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'sku' => 'QS-ADM-01',
            'name' => 'Produto administrativo',
            'category' => 'Laboratório',
            'description' => 'Criado em teste.',
            'price' => 99.90,
            'stock' => 7,
            'active' => true,
            'role' => 'admin',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'QS-ADM-01',
            'stock' => 7,
            'active' => true,
        ]);
    }

    public function test_admin_receives_validation_errors_and_invalid_product_is_not_created(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'sku' => 'QS-ADM-02',
                'name' => '',
                'category' => 'Laboratório',
                'price' => -1,
                'stock' => 1.5,
                'active' => true,
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors(['name', 'price', 'stock']);

        $this->assertDatabaseMissing('products', ['sku' => 'QS-ADM-02']);
    }

    public function test_admin_can_update_product_without_changing_its_sku(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['sku' => 'QS-ADM-03', 'stock' => 3]);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'sku' => 'QS-ADM-03',
            'name' => $product->name,
            'category' => $product->category,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => 12,
            'active' => true,
        ])->assertRedirect(route('admin.products.index'));

        $this->assertSame(12, $product->refresh()->stock);
    }
}
