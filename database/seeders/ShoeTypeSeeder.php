<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShoeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Сапоги',
            'Ботинки',
            'Кроссовки',
            'Туфли',
            'Босоножки',
            'Лоферы',
            'Тапочки',
            'Мокасины',
        ];

        foreach ($types as $typeName) {
            DB::table('shoe_types')->updateOrInsert(
                ['name' => $typeName],
                [
                    'price_cutting' => 1.00,
                    'price_sewing' => 1.00,
                    'price_shoemaker' => 1.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
