<?php

use App\Models\ActionType as T;
use Illuminate\Database\Seeder;

class ActionTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $actionTypes = [
            'proposal_created'       => 'Создание заявки',
            'ware_price_change'      => 'Изменение цены на товар',
            'proposal_status_change' => 'Изменение статуса заявок',
            'user_auth'              => 'Авторизация пользователя',
            'report_creation'        => 'Проведение отчета',
            'task_delegate'          => 'Делегация задачи',
            'task_completed'         => 'Выполнение задачи',
            'money_request'          => "Запрос денег",
            'hide_ware'              => 'Скрытие товара',
            'show_ware'              => 'Публикация товара',
            'workers_block'          => 'Блокировка производства',
            'user_invite'            => 'Приглашение пользователя',
            'revoke_user_access'     => 'Увольнение пользователя',
            'close_warranty_case'    => 'Закрываем гарантийный случай',
            'proposal_notes_change'  => 'Изменение комментария к заявке',
            'change_user_password'   => 'Изменение пароля',
        ];

        foreach ($actionTypes as $key => $value) {
            $t              = new T();
            $t->action_type = $value;
            $t->slug        = $key;
            $t->save();
        }
    }
}
