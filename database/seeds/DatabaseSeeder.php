<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(ActionTypesTableSeeder::class);
        $this->call(StockDataSeeder::class);
        $this->call(WareOrderTableSeeder::class);
        $this->call(PursesTableSeeder::class);
        $this->call(TaxesTableSeeder::class);
        $this->call(DemoAccountingTableSeeder::class);
        $this->call(ProposalStatusTableSeeder::class);
        $this->call(InventoryTableSeeder::class);
    }
}
