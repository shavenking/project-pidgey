<?php

use App\CostType;
use Illuminate\Database\Seeder;

class CostTypesTableSeeder extends Seeder
{
    private $costTypes = ['工資 L', '材料 M', '工料 S', '其它 R'];

    public function run()
    {
        foreach ($this->costTypes as $costType) {
            DB::table((new CostType)->getTable())->insert(['name' => $costType]);
        }
    }
}
