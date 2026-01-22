<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Translation\t;

class MaterialTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Кожа КРС', 'unit' => 3, 'description' => 'Кожа крупного рогатого скота'],
            ['name' => 'Кожа Одежка', 'unit' => 3, 'description' => 'Кожа козы, мягкая'],
            ['name' => 'Кожзаменитель', 'unit' => 3, 'description' => 'Дермонтин'],
            ['name' => 'Межподкладка', 'unit' => 3, 'description' => 'Уплотнитель для кожи'],
            ['name' => 'Тексон', 'unit' => 3, 'description' => 'Материал для стелек'],
            ['name' => 'Гранитоль', 'unit' => 3, 'description' => 'Материал для задникив, подносков'],
            ['name' => 'Поролон', 'unit' => 3, 'description' => ''],
            ['name' => 'Шнурки', 'unit' => 2, 'description' => ''],
            ['name' => 'Липучки', 'unit' => 1, 'description' => 'Мягкая и жесткая липучка'],
            ['name' => 'Резинки', 'unit' => 1, 'description' => ''],
            ['name' => 'Змейки', 'unit' => 2, 'description' => ''],
            ['name' => 'Фурнитура', 'unit' => 2, 'description' => 'Пряжки, застежки, etc'],
            ['name' => 'Декорации', 'unit' => 0, 'description' => 'Нашивки, барашки, etc'],
            ['name' => 'Расходники', 'unit' => 0, 'description' => 'Гвозди, нитки, клей, etc'],
        ];

        foreach ($types as $type) {
            DB::table('material_types')->updateOrInsert(
                ['name' => $type['name']],
                [
                    'unit_id' => $type['unit'],
                    'description' => $type['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
