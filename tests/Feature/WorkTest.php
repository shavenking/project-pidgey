<?php

namespace Tests\Feature;

use App\{
    Work,
    WorkItem
};
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WorkTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetList()
    {
        $work = factory(Work::class)->times(3)->create();

        $response = $this->jsonWithToken('GET', '/api/v1/works')
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

    public function testCreate()
    {
        $work = factory(Work::class)->make();

        $response = $this->jsonWithToken('POST', '/api/v1/works', [
            'name' => $work->name,
            'amount' => $work->amount,
            'engineering_type_id' => $work->engineering_type_id
        ])->assertStatus(201)->decodeResponseJson();

        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);
        $this->assertArrayHasKey('id', $response['data']);
        $this->assertArrayHasKey('name', $response['data']);
        $this->assertArrayHasKey('amount', $response['data']);
        $this->assertArrayHasKey('unit_price', $response['data']);
        $this->assertArrayHasKey('engineering_type_id', $response['data']);

        // engineering_type
        $this->assertArrayHasKey('engineering_type', $response['data']);
        $this->assertArrayHasKey('main_title', $response['data']['engineering_type']);
        $this->assertArrayHasKey('detailing_title', $response['data']['engineering_type']);
    }

    public function testDelete()
    {
        $work = factory(Work::class)->create();
        $workItem = factory(WorkItem::class)->create();
        $work->workItems()->attach($workItem, ['amount' => '1', 'unit_price' => '2']);

        $this->assertDatabaseHas($work->workItems()->getTable(), [
            'work_id' => $work->id,
            'work_item_id' => $workItem->id
        ]);

        $this->jsonWithToken('DELETE', "/api/v1/works/{$work->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing($work->getTable(), ['id' => $work->id]);
        $this->assertDatabaseMissing($work->workItems()->getTable(), [
            'work_id' => $work->id,
            'work_item_id' => $workItem->id
        ]);
    }
}
