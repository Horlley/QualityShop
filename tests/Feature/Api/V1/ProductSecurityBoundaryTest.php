<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductSecurityBoundaryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_category_filter_treats_sql_metacharacters_as_data(): void
    {
        Product::factory()->create(['category' => 'Casa']);
        Product::factory()->create(['category' => 'Eletrônicos']);

        $query = http_build_query(['category' => "' OR 1=1 --"]);

        $response = $this->getJson("/api/v1/products?{$query}");

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_unexpected_id_does_not_replace_server_generated_identifier(): void
    {
        $payload = [
            'id' => 999999,
            'sku' => 'QS-SEC-01',
            'name' => 'Produto controlado pelo servidor',
            'category' => 'Segurança',
            'description' => null,
            'price' => 49.90,
            'stock' => 5,
            'active' => true,
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.sku', 'QS-SEC-01')
            ->assertJsonMissing(['id' => 999999]);
        $this->assertDatabaseHas('products', ['sku' => 'QS-SEC-01']);
        $this->assertDatabaseMissing('products', ['id' => 999999]);
    }

    public function test_validation_failure_does_not_expose_internal_debug_details(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'sku' => 'QS-SEC-02',
            'name' => 'Produto inválido',
            'category' => 'Segurança',
            'price' => 10,
            'stock' => -1,
            'active' => true,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['stock']);

        $this->assertArrayNotHasKey('exception', $response->json());
        $this->assertArrayNotHasKey('trace', $response->json());
        $this->assertDatabaseMissing('products', ['sku' => 'QS-SEC-02']);
    }
}
