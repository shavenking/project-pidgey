<?php

namespace Tests\Feature;

use App\Work;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WorkTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetList()
    {
        $work = factory(Work::class)->times(3)->create();

        $response = $this->json('GET', '/api/v1/works')
            ->assertStatus(200)
            ->decodeResponseJson();

        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);

        foreach ($response['data'] as $work) {
            $this->assertArrayHasKey('id', $work);
            $this->assertArrayHasKey('name', $work);
            $this->assertArrayHasKey('amount', $work);
            $this->assertArrayHasKey('unit_price', $work);
            $this->assertArrayHasKey('engineering_type_id', $work);

            // engineering_type
            $this->assertArrayHasKey('engineering_type', $work);
            $this->assertArrayHasKey('main_title', $work['engineering_type']);
            $this->assertArrayHasKey('detailing_title', $work['engineering_type']);
        }
    }
}
