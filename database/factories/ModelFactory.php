<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\CostType::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text(10)
    ];
});

$factory->define(App\Work::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'amount' => $faker->randomFloat(2, 0, 20),
        'unit_price' => $faker->randomFloat(2, 0, 20),
        'engineering_type_id' => function () {
            return App\EngineeringType::first()->id;
        }
    ];
});

$factory->define(App\WorkItem::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'unit_id' => function () {
            return App\Unit::first()->id;
        },
        'cost_type_id' => function () {
            return App\CostType::first()->id;
        }
    ];
});
