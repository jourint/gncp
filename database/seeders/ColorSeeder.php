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
            ['name' => 'Черный', 'hex' => '#000000'],
            ['name' => 'Белый', 'hex' => '#FFFFFF'],
            ['name' => 'Бежевый', 'hex' => '#F5F5DC'],
            ['name' => 'Красный', 'hex' => '#FF0000'],
            ['name' => 'Бордовый', 'hex' => '#800000'],
            ['name' => 'Темно-синий', 'hex' => '#000080'],
            ['name' => 'Коричневый', 'hex' => '#8B4513'],
            ['name' => 'Пудра', 'hex' => '#FFD1DC'],
            ['name' => 'Капучино', 'hex' => '#A18E83'],
            ['name' => 'Латте', 'hex' => '#C8AD7F'],
            ['name' => 'Серый', 'hex' => '#808080'],
            ['name' => 'Синий', 'hex' => '#0000FF'],
            ['name' => 'Зеленый', 'hex' => '#008000'],
            ['name' => 'Оранжевый', 'hex' => '#FFA500'],
            ['name' => 'Желтый', 'hex' => '#FFFF00'],
            ['name' => 'Розовый', 'hex' => '#FFC0CB'],
            ['name' => 'Фиолетовый', 'hex' => '#800080'],
            ['name' => 'Хаки', 'hex' => '#F0E68C'],
        ];

        foreach ($colors as $color) {
            // Используем updateOrInsert, чтобы не было ошибок при повторном запуске
            DB::table('colors')->updateOrInsert(
                ['name' => $color['name']],
                ['hex' => $color['hex']]
            );
        }
    }
}
