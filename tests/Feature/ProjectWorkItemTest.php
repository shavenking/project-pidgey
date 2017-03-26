<?php

namespace Tests\Feature;

use App\{
    Project,
    ProjectWork,
    ProjectWorkItem,
    WorkItem
};
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectWorkItemTest extends TestCase
{
    use DatabaseTransactions;

    public function testList()
    {
        $project = factory(Project::class)->create();
        $work = factory(ProjectWork::class)->create(['project_id' => $project->id]);
        $workItems = factory(ProjectWorkItem::class, 2)->create(['project_id' => $project->id]);
        $work->workItems()->attach($workItems, [
            'amount' => '10.00', 'unit_price' => '00.10'
        ]);

        // 其它專案的工料項目不應該顯示
        factory(ProjectWork::class)->create()->workItems()->attach($workItems, [
            'amount' => '10.00', 'unit_price' => '00.10'
        ]);

        $this->user = $work->project->user;
        $response = $this->jsonWithToken('GET', "/api/v1/projects/{$project->id}/works/{$work->id}/work-items");

        $response->assertStatus(200)->assertExactJson([
            'data' => $work->workItems->map(function ($workItem) use ($work) {
                $base = array_only($workItem->toArray(), [
                    'id', 'project_id', 'unit_id', 'cost_type_id', 'name'
                ]);

                return array_merge($base, [
                    'project_work_id' => $work->id,
                    'amount' => $workItem->pivot->amount,
                    'unit_price' => $workItem->pivot->unit_price,
                    'unit_name' => $workItem->unit->name,
                    'cost_type_name' => $workItem->costType->name
                ]);
            })->toArray()
        ]);
    }

    /**
     * 跟標準工料功能類似，此 API 會顯示該專案下，所有可用的工料項目，以供快速選擇使用
     */
    public function testListWithoutWork()
    {
        $project = factory(Project::class)->create();
        $workItems = factory(ProjectWorkItem::class, 2)->create(['project_id' => $project->id]);

        // 其它專案的工料項目不會顯示
        factory(ProjectWorkItem::class)->create();

        $this->user = $project->user;
        $this->jsonWithToken('GET', "/api/v1/projects/{$project->id}/work-items")
            ->assertStatus(200)
            ->assertExactJson([
                'data' => $workItems->map(function ($workItem) {
                    $base = array_only($workItem->toArray(), [
                        'id', 'project_id', 'unit_id', 'cost_type_id', 'name'
                    ]);

                    return array_merge($base, [
                        'unit_name' => $workItem->unit->name,
                        'cost_type_name' => $workItem->costType->name
                    ]);
                })->toArray()
            ]);
    }

    /**
     * 使用者可以選擇既有的「專案工料」，新增至指定的專案工項
     */
    public function testUserCanAddExistingProjectWorkItemToProjectWork()
    {
        $project = factory(Project::class)->create();
        $workItem = factory(ProjectWorkItem::class)->create(['project_id' => $project->id]);
        $work = factory(ProjectWork::class)->create(['project_id' => $project->id]);

        $this->user = $project->user;
        $response = $this->jsonWithToken('POST', "/api/v1/projects/{$project->id}/works/{$work->id}/work-items", [
            'project_work_item_id' => $workItem->id,
            'amount' => '10.00',
            'unit_price' => '00.10'
        ]);

        $workItem = $work->workItems()->find($workItem->id);

        $response->assertStatus(201)->assertExactJson([
            'data' => array_merge(
                array_only($workItem->toArray(), [
                    'id', 'project_work_item_id', 'unit_id', 'cost_type_id', 'name'
                ]), [
                    'amount' => $workItem->pivot->amount,
                    'unit_price' => $workItem->pivot->unit_price,
                    'unit_name' => $workItem->unit->name,
                    'cost_type_name' => $workItem->costType->name
                ]
            )
        ]);

        $this->assertDatabaseHas($work->getTable(), [
            'id' => $work->id,
            'unit_price' => bcadd($work->unit_price, '1.00', 2)
        ]);
    }

    /**
     * 使用者可以選擇既有的「標準工料」，新增至指定的工作項目
     */
    public function testUserCanAddExistingWorkItemToProjectWork()
    {
        $project = factory(Project::class)->create();
        $work = factory(ProjectWork::class)->create(['project_id' => $project->id]);
        $workItem = factory(WorkItem::class)->create();

        $this->user = $project->user;
        $response = $this->jsonWithToken('POST', "/api/v1/projects/{$project->id}/works/{$work->id}/work-items", [
            'work_item_id' => $workItem->id,
            'amount' => '10.00',
            'unit_price' => '00.10'
        ]);

        $workItem = $work->workItems()->whereName($workItem->name)->first();

        $response->assertStatus(201)->assertExactJson([
            'data' => array_merge(
                array_only($workItem->toArray(), [
                    'id', 'project_work_item_id', 'unit_id', 'cost_type_id', 'name'
                ]), [
                    'amount' => $workItem->pivot->amount,
                    'unit_price' => $workItem->pivot->unit_price,
                    'unit_name' => $workItem->unit->name,
                    'cost_type_name' => $workItem->costType->name
                ]
            )
        ]);

        $this->assertDatabaseHas($work->getTable(), [
            'id' => $work->id,
            'unit_price' => bcadd($work->unit_price, '1.00', 2)
        ]);
    }

    /**
     * 使用者可以新增全新的專案工料至指定的專案工項
     */
    public function testUserCanCreateProjectWorkItemToProjectWork()
    {
        $project = factory(Project::class)->create();
        $work = factory(ProjectWork::class)->create(['project_id' => $project->id]);
        $workItem = factory(ProjectWorkItem::class)->make();

        $this->user = $project->user;
        $response = $this->jsonWithToken('POST', "/api/v1/projects/{$project->id}/works/{$work->id}/work-items", [
            'unit_id' => $workItem->unit_id,
            'cost_type_id' => $workItem->cost_type_id,
            'name' => $workItem->name,
            'amount' => '10.00',
            'unit_price' => '00.10'
        ]);

        $workItem = $work->workItems()->whereName($workItem->name)->first();

        $response->assertStatus(201)->assertExactJson([
            'data' => array_merge(
                array_only($workItem->toArray(), [
                    'id', 'project_work_item_id', 'unit_id', 'cost_type_id', 'name'
                ]), [
                    'amount' => $workItem->pivot->amount,
                    'unit_price' => $workItem->pivot->unit_price,
                    'unit_name' => $workItem->unit->name,
                    'cost_type_name' => $workItem->costType->name
                ]
            )
        ]);

        $this->assertDatabaseHas($work->getTable(), [
            'id' => $work->id,
            'unit_price' => bcadd($work->unit_price, '1.00', 2)
        ]);
    }

    public function testUserCanNotCreateDuplicateProjectWorkItem()
    {
        $project = factory(Project::class)->create();
        $work = factory(ProjectWork::class)->create(['project_id' => $project->id]);
        $workItem = factory(ProjectWorkItem::class)->create(['project_id' => $project->id]);

        $this->user = $project->user;
        $this->jsonWithToken('POST', "/api/v1/projects/{$project->id}/works/{$work->id}/work-items", [
            'unit_id' => $workItem->unit_id,
            'cost_type_id' => $workItem->cost_type_id,
            'name' => $workItem->name,
            'amount' => '10.00',
            'unit_price' => '00.10'
        ])->assertStatus(409);
    }

    public function testDelete()
    {
        $project = factory(Project::class)->create();
        $work = factory(ProjectWork::class)->create(['project_id' => $project->id, 'unit_price' => '1.00']);
        $workItem = factory(ProjectWorkItem::class)->create(['project_id' => $project->id]);

        $work->workItems()->attach($workItem, ['amount' => '10.00', 'unit_price' => '0.10']);

        $this->user = $project->user;
        $this->jsonWithToken('DELETE', "/api/v1/projects/{$project->id}/works/{$work->id}/work-items/{$workItem->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing($work->workItems()->getTable(), [
            'project_work_id' => $work->id,
            'project_work_item_id' => $workItem->id
        ]);
        $this->assertDatabaseHas($work->getTable(), ['id' => $work->id, 'unit_price' => '0.00']);
    }
}
