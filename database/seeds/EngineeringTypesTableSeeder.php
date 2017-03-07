<?php

use App\EngineeringType;
use Illuminate\Database\Seeder;

class EngineeringTypesTableSeeder extends Seeder
{
    private $engineeringTypes = [
        '基礎工程' => ['反循環基樁', '鋼板樁', '預壘樁', '中間柱H型鋼支柱', 'H型鋼支撐', '地下室抽水', '地下室挖土', '廢土棄運單程距離', '挖土、填土', '基礎伸縮縫'],
        '結構工程' => ['鋼筋', '鋼結構', '模板', '混凝土', '施工大型機具','鷹架'],
        '內外裝飾工程' => ['砌磚', '1:2水泥', '1:3水泥', '內牆磁磚', '外牆磁磚', '地坪屋頂'],
        '地坪及屋頂防水層' => ['地坪', '屋頂', '地板'],
        '其它及門窗工程' => ['鋁窗', '不鏽鋼', '鐵捲門', '隔牆', '門', '扶手']
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->engineeringTypes as $mainTitle => $detailingTitles) {
            foreach ($detailingTitles as $detailingTitle) {
                DB::table((new EngineeringType)->getTable())->insert([
                    'main_title' => $mainTitle,
                    'detailing_title' => $detailingTitle
                ]);
            }
        }
    }
}
