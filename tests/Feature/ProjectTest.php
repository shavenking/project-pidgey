<?php

namespace Tests\Feature;

use App\{
    User,
    Project
};
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetProjects()
    {
        $this->user = factory(User::class)->create();
        $projects = factory(Project::class, 2)->create(['user_id' => $this->user->id]);

        // create a project that belongs to others,
        // this project will not returned by this api
        factory(Project::class)->create();

        $this->jsonWithToken('GET', '/api/v1/projects')
            ->assertStatus(200)
            ->assertExactJson([
                'data' => $projects->toArray()
            ]);
    }
}
