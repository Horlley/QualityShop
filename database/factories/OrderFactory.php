<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'number' => 'QS-'.now()->format('Ymd').'-'.Str::upper(fake()->unique()->bothify('??##??')),
            'status' => 'paid',
            'subtotal' => 120.00,
            'coupon_code' => null,
            'discount' => 0,
            'total' => 120.00,
            'paid_at' => now(),
            'cancelled_at' => null,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
