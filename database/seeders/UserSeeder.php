<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'André Auditor', 'email' => 'auditor@qualityshop.local', 'role' => 'auditor', 'active' => true],
            ['name' => 'Diego Cliente', 'email' => 'cliente2@qualityshop.local', 'role' => 'customer', 'active' => true],
            ['name' => 'Carla Cliente', 'email' => 'cliente@qualityshop.local', 'role' => 'customer', 'active' => true],
            ['name' => 'Otávio Operador', 'email' => 'operador@qualityshop.local', 'role' => 'operator', 'active' => true],
            ['name' => 'Giovana Gerente', 'email' => 'gerente@qualityshop.local', 'role' => 'manager', 'active' => true],
            ['name' => 'Alice Administradora', 'email' => 'admin@qualityshop.local', 'role' => 'admin', 'active' => true],
            ['name' => 'Bruno Bloqueado', 'email' => 'bloqueado@qualityshop.local', 'role' => 'customer', 'active' => false],
        ];

        foreach ($users as $user) {
            User::query()->firstOrCreate(
                ['email' => $user['email']],
                $user + ['password' => 'Quality123!', 'email_verified_at' => now()],
            );
        }
    }
}
