<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_home_page_presents_the_qualityshop_laboratory(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertViewIs('welcome')
            ->assertSeeTextInOrder([
                'Aprenda QA em um produto',
                'Ambiente local pronto para os testes',
                'Uma compra, muitas perguntas de qualidade',
            ]);
    }

    public function test_health_endpoint_reports_that_the_application_is_ready(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }
}
