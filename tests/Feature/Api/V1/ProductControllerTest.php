<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_products_in_stable_order_and_applies_filters(): void
    {
        Product::factory()->create(['sku' => 'QS-101', 'category' => 'Eletrônicos', 'active' => true]);
        Product::factory()->inactive()->create(['sku' => 'QS-102', 'category' => 'Eletrônicos']);
        Product::factory()->create(['sku' => 'QS-103', 'category' => 'Casa', 'active' => true]);

        $response = $this->getJson('/api/v1/products?category=Eletrônicos&active=1');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'QS-101');
    }

    public function test_returns_422_when_list_filter_is_invalid(): void
    {
        $response = $this->getJson('/api/v1/products?active=talvez');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['active'])
            ->assertJsonPath('message', 'O filtro active deve ser verdadeiro ou falso.');
    }

    public function test_returns_one_product_by_id(): void
    {
        $product = Product::factory()->create(['sku' => 'QS-201', 'name' => 'Produto de teste']);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.sku', 'QS-201')
            ->assertJsonPath('data.name', 'Produto de teste');
    }

    public function test_returns_404_when_product_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/products/999999');

        $response->assertNotFound();
    }

    public function test_valid_payload_creates_product_and_returns_201(): void
    {
        $payload = [
            'sku' => 'QS-301',
            'name' => 'Mouse Órbita',
            'category' => 'Eletrônicos',
            'description' => 'Mouse sem fio didático.',
            'price' => 89.90,
            'stock' => 20,
            'active' => true,
            'internal_note' => 'não deve ser aceito',
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.sku', 'QS-301')
            ->assertJsonPath('data.price', '89.90')
            ->assertJsonMissing(['internal_note' => 'não deve ser aceito']);
        $this->assertDatabaseHas('products', [
            'sku' => 'QS-301',
            'stock' => 20,
            'active' => true,
        ]);
    }

    public function test_returns_422_and_does_not_create_product_when_payload_is_invalid(): void
    {
        $payload = [
            'sku' => 'QS-302',
            'name' => '',
            'category' => 'Eletrônicos',
            'price' => -1,
            'stock' => 1.5,
            'active' => 'talvez',
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price', 'stock', 'active']);
        $this->assertDatabaseMissing('products', ['sku' => 'QS-302']);
    }

    public function test_partial_payload_updates_only_informed_fields(): void
    {
        $product = Product::factory()->create([
            'sku' => 'QS-401',
            'name' => 'Produto original',
            'stock' => 10,
            'active' => true,
        ]);

        $response = $this->patchJson("/api/v1/products/{$product->id}", [
            'stock' => 0,
            'active' => false,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Produto original')
            ->assertJsonPath('data.stock', 0)
            ->assertJsonPath('data.active', false);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Produto original',
            'stock' => 0,
            'active' => false,
        ]);
    }

    public function test_delete_returns_204_and_removes_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertNoContent();
        $this->assertModelMissing($product);
    }
}
