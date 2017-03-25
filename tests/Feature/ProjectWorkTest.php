<?php

namespace Tests\Feature;

use App\{
    Project,
    ProjectWork
};
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectWorkTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetList()
    {
        $project = factory(Project::class)->create();
        $works = factory(ProjectWork::class, 2)->create(['project_id' => $project->id]);
        $this->user = $project->user;

        // work that belongs to other project should not be returned
        factory(ProjectWork::class)->create();

        $this->jsonWithToken('GET', "/api/v1/projects/{$project->id}/works")
            ->assertStatus(200)
            ->assertExactJson([
                'data' => $works->map(function ($work) {
                    $base = array_only($work->toArray(), [
                        'id', 'name', 'amount', 'unit_price', 'engineering_type_id', 'project_id'
                    ]);

                    return array_merge($base, [
                        'engineering_type_main_title' => $work->engineeringType->main_title,
                        'engineering_type_detailing_title' => $work->engineeringType->detailing_title
                    ]);
                })->toArray()
            ]);
    }

    public function testUserThatIsNotProjectOwnerCanNotGetList()
    {
        $project = factory(Project::class)->create();
        $works = factory(ProjectWork::class, 2)->create(['project_id' => $project->id]);

        $this->jsonWithToken('GET', "/api/v1/projects/{$project->id}/works")
            ->assertStatus(403);
    }
}
