<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductionRouteTest extends TestCase
{
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_report_routes_exist(): void
    {
        $this->assertTrue(route('reportes.index') !== null);
        $this->assertTrue(route('reportes.produccion') !== null);
        $this->assertTrue(route('reportes.inventario') !== null);
    }
}
