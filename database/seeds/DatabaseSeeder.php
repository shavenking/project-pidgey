<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $this->call(UnitsTableSeeder::class);
        });
    }
}
