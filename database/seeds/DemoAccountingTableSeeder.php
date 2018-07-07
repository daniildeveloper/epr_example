<?php
use App\Models\AccountingPeriodEnd as P;
use App\Models\AccountingPeriodEndPurseDetail as Detail;
use App\Models\Purse;
use Illuminate\Database\Seeder;

class DemoAccountingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $purses = Purse::all();

        $p = new P();
        $p->save();

        foreach ($purses as $purse) {
            $d                = new Detail();
            $d->purse_id      = $purse->id;
            $d->rest          = 0;
            $d->accounting_id = $p->id;
            $d->save();
        }
    }
}
