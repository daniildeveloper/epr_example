<?php

use App\Models\ProfitCoordinator;
use App\Models\Purse;
use App\Models\PurseCategory;
use Illuminate\Database\Seeder;

class PursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $salersPurseCategory             = new PurseCategory();
        $salersPurseCategory->name       = 'Кошелек офиса';
        $salersPurseCategory->permission = 'sale_finances';
        $salersPurseCategory->save();

        $systemPurseCategory             = new PurseCategory();
        $systemPurseCategory->name       = 'Кошелек системы';
        $systemPurseCategory->permission = 'finances';
        $systemPurseCategory->save();

        $foundsPurseCategory             = new PurseCategory();
        $foundsPurseCategory->name       = 'Фонды';
        $foundsPurseCategory->permission = 'finances';
        $foundsPurseCategory->save();

        $chemiePurseCategory             = new PurseCategory();
        $chemiePurseCategory->name       = 'Покупка химии';
        $chemiePurseCategory->permission = 'show_stock_privats';
        $chemiePurseCategory->save();

        $workersPurseCategory             = new PurseCategory();
        $workersPurseCategory->name       = 'Кошелек цеха';
        $workersPurseCategory->permission = 'workers_purse';
        $workersPurseCategory->save();

        // create salers purses
        $salersMoneyPurse              = new Purse();
        $salersMoneyPurse->name        = 'Касса офиса';
        $salersMoneyPurse->permission  = 'sale_finances';
        $salersMoneyPurse->category_id = $salersPurseCategory->id;
        $salersMoneyPurse->slug        = 'salers_money';
        $salersMoneyPurse->save();

        $salersMoneyChemiePurse              = new Purse();
        $salersMoneyChemiePurse->name        = 'Оборотные деньги';
        $salersMoneyChemiePurse->category_id = $salersPurseCategory->id;
        $salersMoneyChemiePurse->permission  = 'sale_finances';
        $salersMoneyChemiePurse->slug        = 'sale_oborot';
        $salersMoneyChemiePurse->save();

        $salersMoneyPackagingPurse              = new Purse();
        $salersMoneyPackagingPurse->name        = 'Кошелек для оплаты упаковок и наклеек';
        $salersMoneyPackagingPurse->category_id = $salersPurseCategory->id;
        $salersMoneyPackagingPurse->permission  = 'sale_finances';
        $salersMoneyPackagingPurse->slug        = 'sale_packagings';
        $salersMoneyPackagingPurse->save();

        // create system purses
        $systemTaxesPurse              = new Purse();
        $systemTaxesPurse->name        = 'Кошелек для уплаты налогов';
        $systemTaxesPurse->permission  = 'finances';
        $systemTaxesPurse->category_id = $systemPurseCategory->id;
        $systemTaxesPurse->slug        = 'system_tax';
        $systemTaxesPurse->save();

        $affialateProgramPurse              = new Purse();
        $affialateProgramPurse->name        = 'Кошелек для выплат по партнерской программе';
        $affialateProgramPurse->category_id = $systemPurseCategory->id;
        $affialateProgramPurse->slug        = 'affialate';
        $affialateProgramPurse->permission  = 'sale_finances';
        $affialateProgramPurse->save();

        $salersProfitPurse              = new Purse();
        $salersProfitPurse->name        = 'Кошелек прибыли офиса';
        $salersProfitPurse->slug        = 'salers_profit_purse';
        $salersProfitPurse->permission  = 'sale_finances';
        $salersProfitPurse->category_id = $systemPurseCategory->id;
        $salersProfitPurse->save();

        $workersProfitPurse              = new Purse();
        $workersProfitPurse->name        = 'Кошелек прибыли цеха';
        $workersProfitPurse->slug        = 'workers_profit_purse';
        $workersProfitPurse->permission  = 'workers_purse';
        $workersProfitPurse->category_id = $systemPurseCategory->id;
        $workersProfitPurse->save();

        // workers purse
        $workersPurse              = new Purse();
        $workersPurse->name        = 'Кошелек цеха';
        $workersPurse->slug        = 'workers_purse';
        $workersPurse->permission  = 'workers_purse';
        $workersPurse->category_id = $workersPurseCategory->id;
        $workersPurse->save();

        // Кошелек для покупки химии
        $chemiePurse              = new Purse();
        $chemiePurse->name        = 'Кошелек для покупки химии';
        $chemiePurse->slug        = 'chemie_purse';
        $chemiePurse->permission  = 'workers_purse';
        $chemiePurse->category_id = $chemiePurseCategory->id;
        $chemiePurse->save();

        $warantyPurse = new Purse();
        $warantyPurse->name = 'Гарантийные случаи';
        $warantyPurse->slug = 'warranty_purse';
        $warantyPurse->permission = 'finances';
        $warantyPurse->category_id = $systemPurseCategory->id;
        $warantyPurse->save();

        $pc                 = new ProfitCoordinator();
        $pc->sale_profit    = 70;
        $pc->workers_profit = 30;
        $pc->save();
    }
}
