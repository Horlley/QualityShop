<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_presents_the_didactic_profiles(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSeeText('Cliente')
            ->assertSeeText('Administrador')
            ->assertSeeText('Usuário bloqueado');
    }

    public function test_active_user_can_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'cliente@qualityshop.local',
            'password' => Hash::make('Quality123!'),
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Quality123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_and_blocked_users_receive_the_same_safe_message(): void
    {
        $blockedUser = User::factory()->blocked()->create([
            'email' => 'bloqueado@qualityshop.local',
            'password' => Hash::make('Quality123!'),
        ]);

        foreach ([
            ['email' => 'inexistente@qualityshop.local', 'password' => 'errada'],
            ['email' => $blockedUser->email, 'password' => 'Quality123!'],
        ] as $credentials) {
            $this->from(route('login'))->post(route('login.store'), $credentials)
                ->assertRedirect(route('login'))
                ->assertSessionHasErrors(['email' => 'As credenciais são inválidas ou o usuário está bloqueado.']);
            $this->assertGuest();
        }
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
