<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            ['name' => 'Белый', 'hex' => '#FFFFFF', 'is_active' => true],
            ['name' => 'Черный', 'hex' => '#000000', 'is_active' => true],
            ['name' => 'Красный', 'hex' => '#FF0000', 'is_active' => true],
            ['name' => 'Бежевый', 'hex' => '#F5F5DC', 'is_active' => true],
            ['name' => 'Пудра', 'hex' => '#FFD1DC', 'is_active' => true],
            ['name' => 'Капучино', 'hex' => '#A18E83', 'is_active' => true],
            ['name' => 'Латте', 'hex' => '#C8AD7F', 'is_active' => true],
            ['name' => 'Лотос', 'hex' => '#dfe2e2', 'is_active' => true],
            ['name' => 'Коричневый', 'hex' => '#8B4513', 'is_active' => false],
            ['name' => 'Коралловый', 'hex' => '#f1600c', 'is_active' => true],
            ['name' => 'Бирюзовый', 'hex' => '#008080', 'is_active' => false],
            ['name' => 'Желтый', 'hex' => '#FFFF00', 'is_active' => true],
            ['name' => 'Лавандовый', 'hex' => '#00FF00', 'is_active' => false],
            ['name' => 'Синий', 'hex' => '#0000FF', 'is_active' => false],
            ['name' => 'Оранжевый', 'hex' => '#FFA500', 'is_active' => false],
            ['name' => 'Бордовый', 'hex' => '#800000', 'is_active' => false],
            ['name' => 'Зеленый', 'hex' => '#008000', 'is_active' => false],
            ['name' => 'Фиолетовый', 'hex' => '#800080', 'is_active' => false],
            ['name' => 'Хаки', 'hex' => '#F0E68C', 'is_active' => false],
            ['name' => 'Cерый', 'hex' => '#b1aaac', 'is_active' => false],
        ];

        foreach ($colors as $color) {
            DB::table('colors')->updateOrInsert(
                ['name' => $color['name']],
                ['hex' => $color['hex'], 'is_active' => $color['is_active']]
            );
        }
    }
}
