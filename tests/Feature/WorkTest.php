<?php

namespace Tests\Feature;

use App\{
    User,
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
            $this->assertArrayHasKey('unit_id', $work);
            $this->assertArrayHasKey('unit_price', $work);
            $this->assertArrayHasKey('engineering_type_id', $work);

            $this->assertArrayHasKey('unit', $work);
            $this->assertArrayHasKey('name', $work['unit']);

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
            'unit_id' => $work->unit_id,
            'engineering_type_id' => $work->engineering_type_id
        ])->assertStatus(201);

        $work = Work::whereName($work->name)->with('engineeringType', 'unit')->first();

        $response->assertExactJson([
            'data' => $work->toArray()
        ]);
    }

    public function testDelete()
    {
        $this->user = factory(User::class)->create();
        $work = factory(Work::class)->create(['user_id' => $this->user->id]);
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

    public function testOtherUserCanNotDelete()
    {
        $work = factory(Work::class)->create();

        $this->jsonWithToken('DELETE', "/api/v1/works/{$work->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas($work->getTable(), ['id' => $work->id]);
    }
}
