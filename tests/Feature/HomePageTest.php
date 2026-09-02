<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_presents_the_qualityshop_laboratory(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertViewIs('welcome')
            ->assertSeeTextInOrder([
                'QualityShop',
                'Ambiente local pronto para os testes',
                'Uma aplicação que evolui junto com o leitor',
            ]);
    }

    public function test_health_endpoint_reports_that_the_application_is_ready(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }
}
