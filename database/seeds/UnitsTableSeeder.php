<?php

use App\Unit;
use Illuminate\Database\Seeder;

class UnitsTableSeeder extends Seeder
{
    private $units = ['公尺', '平方公尺', '立方公尺', '公斤', '公噸', '工', '小時', '天', '月', '年', '支', '只', '式', '包', '才', '張', '毫升', '公升', '加侖', '處', '部', '%', '次', '塊', '片', '扇', '樘', '付', '組'];

    public function run()
    {
        foreach ($this->units as $unit) {
            DB::table((new Unit)->getTable())->insert(['name' => $unit]);
        }
    }
}
