<?php

use App\Models\ProposalStatus as S;
use Illuminate\Database\Seeder;

class ProposalStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        S::create(['name' => 'Создана', 'slug' => 'created', 'color' => '#455a64']);
        S::create(['name' => 'Непринято', 'slug' => 'not_allowed', 'color' => '#e53935']);
        S::create(['name' => 'Принято в цеху', 'slug' => 'accepted_workers', 'color' => '#3f51b5']);
        S::create(['name' => 'На отмену', 'slug' => 'decline_candidat', 'color' => '#8bc34a']);
        S::create(['name' => 'В процессе', 'slug' => 'in_process', 'color' => '#ffca28']);
        S::create(['name' => 'Готово в цеху', 'slug' => 'ready', 'color' => '#388e3c']);
        S::create(['name' => 'Отправленно', 'slug' => 'sended', 'color' => '#4e342e']);
        S::create(['name' => 'Принято клиентом', 'slug' => 'acepted_by_client', 'color' => '#c2185b']);
        S::create(['name' => 'Отмененный', 'slug' => 'declined', 'color' => '#757575']);
    }
}
