<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MessengerPermission;

class MessengerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'view_salary',
                'label' => 'Просмотр зарплаты (выработки)',
            ],
            [
                'name' => 'view_tech_cards',
                'label' => 'Просмотр тех-карт моделей',
            ],
            [
                'name' => 'manage_warehouse',
                'label' => 'Доступ к остаткам склада',
            ],
            [
                'name' => 'create_order',
                'label' => 'Оформление новых заказов',
            ],
            [
                'name' => 'view_customer_orders',
                'label' => 'Просмотр своих заказов (для клиентов)',
            ],
        ];

        foreach ($permissions as $permission) {
            MessengerPermission::updateOrCreate(
                ['name' => $permission['name']],
                ['label' => $permission['label']]
            );
        }
    }
}
