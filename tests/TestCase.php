<?php

namespace Tests;

use App\User;
use JWTAuth;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function jsonWithToken($method, $uri, array $data = [], array $headers = [])
    {
        $user = factory(User::class)->create();
        $token = JWTAuth::fromUser($user);

        return parent::json($method, $uri, $data, array_merge($headers, [
            'Authorization' => "Bearer $token"
        ]));
    }
}
