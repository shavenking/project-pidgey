<?php

namespace Tests\Feature;

use App\{
    Project,
    ProjectWork,
    ProjectWorkItem
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
}
