<?php

namespace Tests\Feature;

use App\EngineeringType;
use Tests\TestCase;

class EngineeringTypeTest extends TestCase
{
    public function testGetList()
    {
        $response = $this->jsonWithToken('GET', '/api/v1/engineering-types')
            ->assertStatus(200)
            ->decodeResponseJson();

        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);

        foreach ($response['data'] as $engineeringType) {
            $this->assertArrayHasKey('id', $engineeringType);
            $this->assertArrayHasKey('main_title', $engineeringType);
            $this->assertArrayHasKey('detailing_title', $engineeringType);
        }
    }
}
