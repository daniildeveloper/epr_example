<?php

use App\Models\Ware;
use App\Models\WareOrder;
use Illuminate\Database\Seeder;

class WareOrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $wares = Ware::all();

        foreach ($wares as $ware) {
            $wareOrder          = new WareOrder();
            $wareOrder->ware_id = $ware->id;
            $wareOrder->save();
        }
    }
}
