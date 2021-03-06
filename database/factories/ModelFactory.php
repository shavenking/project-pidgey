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
        'unit_price' => (string) $faker->randomFloat(2, 0, 20),
        'unit_id' => function () {
            return App\Unit::first()->id;
        },
        'engineering_type_id' => function () {
            return App\EngineeringType::first()->id;
        },
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
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

$factory->define(App\Project::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
        }
    ];
});

$factory->define(App\ProjectWork::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'amount' => number_format($faker->randomFloat(2, 0, 20), 2),
        'unit_price' => number_format($faker->randomFloat(2, 0, 20), 2),
        'unit_id' => function () {
            return App\Unit::first()->id;
        },
        'engineering_type_id' => function () {
            return App\EngineeringType::first()->id;
        },
        'project_id' => function () {
            return factory(App\Project::class)->create()->id;
        }
    ];
});

$factory->define(App\ProjectWorkItem::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'project_id' => function () {
            return factory(App\Project::class)->create()->id;
        },
        'unit_id' => function () {
            return App\Unit::first()->id;
        },
        'cost_type_id' => function () {
            return factory(App\CostType::class)->create()->id;
        }
    ];
});
