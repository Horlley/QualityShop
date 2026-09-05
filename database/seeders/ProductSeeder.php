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
            ['sku' => 'QS-060', 'name' => 'Kit Essencial QA', 'category' => 'Laboratório', 'description' => 'Dado-base do capítulo 6: duas unidades totalizam R$ 120,00.', 'price' => 60.00, 'stock' => 10, 'active' => true],
            ['sku' => 'QS-040', 'name' => 'Bloco de Evidências', 'category' => 'Laboratório', 'description' => 'Combine com o Kit Essencial para atingir exatamente R$ 100,00.', 'price' => 40.00, 'stock' => 10, 'active' => true],
            ['sku' => 'QS-099', 'name' => 'Kit Fronteira', 'category' => 'Laboratório', 'description' => 'Subtotal de R$ 99,99: limite inferior do cupom.', 'price' => 99.99, 'stock' => 10, 'active' => true],
            ['sku' => 'QS-001', 'name' => 'Fone Aurora', 'category' => 'Eletrônicos', 'description' => 'Fone sem fio para uso cotidiano.', 'price' => 199.90, 'stock' => 15, 'active' => true],
            ['sku' => 'QS-002', 'name' => 'Teclado Horizonte', 'category' => 'Eletrônicos', 'description' => 'Teclado compacto com conexão USB.', 'price' => 249.50, 'stock' => 8, 'active' => true],
            ['sku' => 'QS-003', 'name' => 'Caneca Nebulosa', 'category' => 'Casa', 'description' => 'Caneca de cerâmica com capacidade de 350 ml.', 'price' => 39.90, 'stock' => 0, 'active' => true],
            ['sku' => 'QS-004', 'name' => 'Mochila Atlas', 'category' => 'Acessórios', 'description' => 'Mochila urbana com compartimento para notebook.', 'price' => 189.00, 'stock' => 12, 'active' => false],
            ['sku' => 'QS-005', 'name' => 'Caderno Prisma', 'category' => 'Papelaria', 'description' => 'Caderno pautado com 160 páginas.', 'price' => 24.90, 'stock' => 30, 'active' => true],
            ['sku' => 'QS-006', 'name' => 'Luminária Eclipse', 'category' => 'Casa', 'description' => 'Luminária de mesa com três intensidades.', 'price' => 79.90, 'stock' => 6, 'active' => true],
            ['sku' => 'QS-007', 'name' => 'Mouse Órbita', 'category' => 'Eletrônicos', 'description' => 'Mouse sem fio compacto para estudo e trabalho.', 'price' => 89.90, 'stock' => 20, 'active' => true],
            ['sku' => 'QS-008', 'name' => 'Garrafa Cometa', 'category' => 'Acessórios', 'description' => 'Garrafa térmica reutilizável de 600 ml.', 'price' => 59.90, 'stock' => 4, 'active' => true],
        ];

        foreach ($products as $product) {
            Product::query()->firstOrCreate(
                ['sku' => $product['sku']],
                $product,
            );
        }
    }
}
