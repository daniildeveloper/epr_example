<?php

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pwd   = Hash::make('1234567890');
        $users = [
            [
                'name'     => 'Kamol',
                'email'    => 'integroat@ya.ru',
                'password' => $pwd,
            ],
            [
                'name'     => 'Daniil',
                'email'    => 'daniilborowkow@ya.ru',
                'password' => $pwd,
            ],
            [
                'name'     => 'Директор офиса',
                'email'    => 'saledirector@trigorg.ru',
                'password' => Hash::make('babosiki'),
            ],
            [
                'name'     => 'Продавец',
                'email'    => 'sale@trigorg.ru',
                'password' => Hash::make('travertin'),
            ],
        ];

        foreach ($users as $u) {
            $user           = new User();
            $user->name     = $u['name'];
            $user->email    = $u['email'];
            $user->password = $u['password'];
            $user->save();
        }

        $roles = [
            ['name' => 'worker', 'description' => 'Цеховик'],
            ['name' => 'saler_limited_ops', 'description' => 'Менеджер с ограниченным доступом'],
            ['name' => 'saler', 'description' => 'Менеджер'],
            ['name' => 'directorWorker', 'description' => 'Директор цеха'],
            ['name' => 'directorSale', 'description' => 'Директор офиса'],
            ['name' => 'owner', 'description' => 'Владелец'],
        ];

        foreach ($roles as $role) {
            $r              = new Role();
            $r->name        = $role['name'];
            $r->description = $role['description'];
            $r->save();
        }

        $permissions = [
            ['name' => 'invite_users', 'description' => 'Приглашать пользователей'],
            ['name' => 'revoke_user_access', 'description' => 'Уволнять пользователей'],
            ['name' => 'delegate_task', 'description' => 'Делегировать задачи'],
            ['name' => 'make_inventory', 'description' => 'Проводить инвентаризации'],
            ['name' => 'crud_nomenclatures', 'description' => 'Управлять складской номенлктурой'],
            ['name' => 'show_stock_privats', 'description' => 'Доступ к технологии'],
            ['name' => 'supply', 'description' => 'Управлять поставками и поставщиками'],
            ['name' => 'finances', 'description' => 'Работа с финансами, расчет прибыли, отчеты'],
            ['name' => 'money_request', 'description' => 'Запрос денег'],
            ['name' => 'money_send', 'description' => 'Отправка денег'],
            ['name' => 'actions_archive', 'description' => 'Просмотр всех всех действий'],
            ['name' => 'confirm_proposals', 'description' => 'принимать заявки'],
            ['name' => 'unallow_proposals', 'description' => 'Отменять заявку'],
            ['name' => 'block_stock', 'description' => 'блокировать производство'],
            ['name' => 'longation_accounting_cycle', 'description' => 'доступ к продлению отчетного периода'],
            ['name' => 'sale_finances', 'description' => 'Кошельки офиса'],
            ['name' => 'workers_purse', 'description' => 'Кошельки цеха'],
            ['name' => 'hide_wares', 'description' => 'Скрывать товары'],
            ['name' => 'show_stock_info', 'description' => 'Просмотр товарных номенклатур'],
            ['name' => 'end_accounting_period', 'description' => 'Завершать отчетный период'],
        ];

        foreach ($permissions as $p) {
            $permission              = new Permission();
            $permission->name        = $p['name'];
            $permission->description = $p['description'];
            $permission->save();
        }

        // Create Owner role
        $owner = Role::where('name', 'owner')->first();
        $owner->syncPermissions([
            'invite_users',
            'revoke_user_access',
            'delegate_task',
            'make_inventory',
            'crud_nomenclatures',
            'show_stock_privats',
            'supply',
            'finances',
            'money_request',
            'actions_archive',
            // 'longation_accounting_cycle',
            'block_stock',
            'confirm_proposals',
            // 'money_send',
            'unallow_proposals',
            'workers_purse',
            'hide_wares',
            'show_stock_info',
            'end_accounting_period',
        ]);

        $saleDirector = Role::where('name', 'directorSale')->first();
        $saleDirector->syncPermissions([
            'delegate_task',
            'finances',
            'money_send',
            'sale_finances',
            'show_stock_info',
        ]);

        $workerDirector = Role::where('name', 'directorWorker')->first();
        $workerDirector->syncPermissions([
            'delegate_task',
            'make_inventory',
            'unallow_proposals',
            'show_stock_info',
        ]);

        User::where('email', 'saledirector@trigorg.ru')->first()->assignRole('directorSale');
        User::where('email', 'sale@trigorg.ru')->first()->assignRole('saler');

        $pwd = bcrypt('Proizvodstvo');
        $user = new User();
        $user->name = 'Taurus';
        $user->email = 'integroat@yandex.ru';
        $user->password = $pwd;
        $user->save();
        $user->assignRole('owner');

        $permissions = [
            ['name' => 'change_wares_order', 'description' => 'Менять порядок выдачи товаров'],
            ['name' => 'watch_edit', 'description' => 'Управлять сменами рабочих цеха']
        ];

        foreach ($permissions as $p) {
            $permission              = new Permission();
            $permission->name        = $p['name'];
            $permission->description = $p['description'];
            $permission->save();
        }

        // Create Owner role
        $owner = Role::where('name', 'owner')->first();
        $owner->givePermissionTo('change_wares_order');
    }


}
