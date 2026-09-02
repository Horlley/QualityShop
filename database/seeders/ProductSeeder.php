<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['sku' => 'QS-001', 'name' => 'Fone Aurora', 'category' => 'Eletrônicos', 'description' => 'Fone sem fio para uso cotidiano.', 'price' => 199.90, 'stock' => 15, 'active' => true],
            ['sku' => 'QS-002', 'name' => 'Teclado Horizonte', 'category' => 'Eletrônicos', 'description' => 'Teclado compacto com conexão USB.', 'price' => 249.50, 'stock' => 8, 'active' => true],
            ['sku' => 'QS-003', 'name' => 'Caneca Nebulosa', 'category' => 'Casa', 'description' => 'Caneca de cerâmica com capacidade de 350 ml.', 'price' => 39.90, 'stock' => 0, 'active' => true],
            ['sku' => 'QS-004', 'name' => 'Mochila Atlas', 'category' => 'Acessórios', 'description' => 'Mochila urbana com compartimento para notebook.', 'price' => 189.00, 'stock' => 12, 'active' => false],
            ['sku' => 'QS-005', 'name' => 'Caderno Prisma', 'category' => 'Papelaria', 'description' => 'Caderno pautado com 160 páginas.', 'price' => 24.90, 'stock' => 30, 'active' => true],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                $product,
            );
        }
    }
}
