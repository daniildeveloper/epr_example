<?php

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ip       = new Tax();
        $ip->name = 'ИП';
        $ip->tax  = 3.0;
        $ip->save();

        $too       = new Tax();
        $too->name = 'ТОО';
        $too->tax  = 12.0;
        $too->save();
    }
}
