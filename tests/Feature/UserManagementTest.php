<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function accountData(array $overrides = []): array
    {
        return array_replace(['name' => 'Pessoa Fictícia', 'email' => 'pessoa@example.test', 'role' => 'customer', 'active' => true, 'password' => 'Quality123!', 'password_confirmation' => 'Quality123!'], $overrides);
    }

    public function test_operator_creates_customer_with_hashed_password(): void
    {
        $this->actingAs(User::factory()->operator()->create())->post(route('users.store'), $this->accountData())->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'pessoa@example.test')->sole();
        $this->assertSame('customer', $user->role);
        $this->assertTrue(Hash::check('Quality123!', $user->password));
    }

    public function test_operator_cannot_create_admin_or_edit_account(): void
    {
        $operator = User::factory()->operator()->create();
        $customer = User::factory()->create();

        $this->actingAs($operator)->post(route('users.store'), $this->accountData(['role' => 'admin']))->assertSessionHasErrors('role');
        $this->put(route('users.update', $customer), $this->accountData())->assertForbidden();
        $this->get(route('users.edit', $customer))->assertForbidden();

        $this->assertDatabaseCount('users', 2);
        $this->assertSame('customer', $customer->refresh()->role);
    }

    public function test_admin_updates_profile_and_disables_existing_session(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $this->actingAs($admin)->put(route('users.update', $customer), $this->accountData(['active' => false, 'role' => 'operator', 'password' => null, 'password_confirmation' => null]))->assertRedirect();
        $this->actingAs($customer->refresh())->get(route('dashboard'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'role' => 'operator', 'active' => false]);
    }

    public function test_admin_cannot_remove_own_access_and_duplicate_email_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('users.update', $admin), $this->accountData(['active' => false]))->assertSessionHasErrors('role');
        $this->post(route('users.store'), $this->accountData(['email' => $admin->email]))->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
        $this->assertTrue($admin->refresh()->active);
    }

    public function test_customer_cannot_view_accounts_or_create_another_account(): void
    {
        $this->actingAs(User::factory()->create())->get(route('users.index'))->assertForbidden();
        $this->post(route('users.store'), $this->accountData())->assertForbidden();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_remote_requests_cannot_use_public_didactic_api(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])->getJson('/api/v1/products')->assertForbidden();
    }

    public function test_auditor_reads_orders_but_cannot_mutate_them_or_create_cart(): void
    {
        $order = Order::factory()->create();
        $auditor = User::factory()->create(['role' => 'auditor']);

        $this->actingAs($auditor)->get(route('orders.show', $order))->assertSeeText($order->number)->assertDontSeeText('Cancelar pedido');
        $this->post(route('orders.cancellation.store', $order))->assertForbidden();
        $this->post(route('cart.items.store'), ['product_id' => 1, 'quantity' => 1])->assertForbidden();

        $this->assertSame('paid', $order->refresh()->status);
    }
}
