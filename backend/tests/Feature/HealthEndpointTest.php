<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_api_health_reports_online_payments_only(): void
    {
        $this->getJson('/api/v1/health')->assertOk()->assertJsonPath('status', 'ok')->assertJsonPath('cod_enabled', false);
    }

    public function test_security_headers_are_applied(): void
    {
        $this->get('/up')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
