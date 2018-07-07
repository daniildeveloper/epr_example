<?php

use App\Models\Inventory;
use App\Models\Packaging;
use App\Models\RestFramework as Rest;
use App\Models\Sticker;
use Illuminate\Database\Seeder;

class InventoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rests      = Rest::all();
        $packagings = Packaging::all();
        $stickers   = Sticker::all();

        foreach ($rests as $rest) {
            $i                 = new Inventory();
            $i->answered_id    = 3;
            $i->component_type = 'rest_frameworks';
            $i->component_id   = $rest->id;
            $i->expected_rests = 0;
            $i->expected_sum   = 0;
            $i->real_sum       = 0;
            $i->real_rest      = 0;
            $i->save();
        }

        // stickers inventory
        foreach ($stickers as $sticker) {
            $i                 = new Inventory();
            $i->answered_id    = 3;
            $i->component_type = 'stickers';
            $i->component_id   = $sticker->id;
            $i->expected_rests = 0;
            $i->expected_sum   = 0;
            $i->real_sum       = 0;
            $i->real_rest      = 0;
            $i->save();
        }
        
        // packagings inventory
        foreach ($packagings as $packaging) {
            $i                 = new Inventory();
            $i->answered_id    = 3;
            $i->component_type = 'packagings';
            $i->component_id   = $packaging->id;
            $i->expected_rests = 0;
            $i->expected_sum   = 0;
            $i->real_sum       = 0;
            $i->real_rest      = 0;
            $i->save();
        }

    }
}
