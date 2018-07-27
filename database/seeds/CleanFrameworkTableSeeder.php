<?php

use App\Models\Manufactory\CleanFramework;
use App\Models\Manufactory\CleanFrameworkAccounting;
use App\Models\Manufactory\CleanFrameworkToRestFramework;
use Illuminate\Database\Seeder;

class CleanFrameworkTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CleanFramework::create(['name' => 'Experimental']);

        CleanFrameworkToRestFramework::create([
            'clean_framework_id' => 1,
            'rest_framework_id'  => 1,
            'count'              => 1,
        ]);

        $cleanFrameworks = CleanFramework::all();

        foreach ($cleanFrameworks as $value) {
            CleanFrameworkAccounting::create([
                'clean_framework_id' => $value->id,
                'rest'               => 1,
            ]);
        }

    }
}
