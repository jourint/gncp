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
            ['name' => 'Белый', 'hex' => '#FFFFFF'],
            ['name' => 'Черный', 'hex' => '#000000'],
            ['name' => 'Красный', 'hex' => '#FF0000'],
            ['name' => 'Бежевый', 'hex' => '#F5F5DC'],
            ['name' => 'Пудра', 'hex' => '#FFD1DC'],
            ['name' => 'Капучино', 'hex' => '#A18E83'],
            ['name' => 'Латте', 'hex' => '#C8AD7F'],
            ['name' => 'Лотос', 'hex' => '#dfe2e2'],
            ['name' => 'Коричневый', 'hex' => '#8B4513'],
            ['name' => 'Коралловый', 'hex' => '#f1600c'],
            ['name' => 'Бирюзовый', 'hex' => '#008080'],
            ['name' => 'Желтый', 'hex' => '#FFFF00'],
            ['name' => 'Лавандовый', 'hex' => '#00FF00'],
            ['name' => 'Синий', 'hex' => '#0000FF'],
            ['name' => 'Оранжевый', 'hex' => '#FFA500'],
            ['name' => 'Бордовый', 'hex' => '#800000'],
            ['name' => 'Зеленый', 'hex' => '#008000'],
            ['name' => 'Фиолетовый', 'hex' => '#800080'],
            ['name' => 'Хаки', 'hex' => '#F0E68C'],
        ];

        foreach ($colors as $color) {
            DB::table('colors')->updateOrInsert(
                ['name' => $color['name']],
                ['hex' => $color['hex']]
            );
        }
    }
}
