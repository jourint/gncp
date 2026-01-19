<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Кожа КРС', 'unit' => 'dm2'],
            ['name' => 'Подкладка', 'unit' => 'dm2'],
            ['name' => 'Мех / Байка', 'unit' => 'dm2'],
            ['name' => 'Фурнитура', 'unit' => 'pcs'],
        ];

        foreach ($types as $type) {
            DB::table('material_types')->updateOrInsert(
                ['name' => $type['name']],
                [
                    'unit' => $type['unit'], // Если такого поля нет, просто удалите эту строку
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
