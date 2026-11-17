<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiningTable;
use App\Models\Branch;

class DiningTableSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = Branch::first()?->id;

        $tables = [
            ['table_number' => 'T-01', 'capacity' => 2, 'status' => 'available'],
            ['table_number' => 'T-02', 'capacity' => 2, 'status' => 'available'],
            ['table_number' => 'T-03', 'capacity' => 4, 'status' => 'available'],
            ['table_number' => 'T-04', 'capacity' => 4, 'status' => 'available'],
            ['table_number' => 'T-05', 'capacity' => 6, 'status' => 'available'],
            ['table_number' => 'T-06', 'capacity' => 6, 'status' => 'available'],
            ['table_number' => 'VIP-01', 'capacity' => 10, 'status' => 'available'],
            ['table_number' => 'VIP-02', 'capacity' => 12, 'status' => 'available'],
        ];

        foreach ($tables as $table) {
            DiningTable::firstOrCreate(
                ['table_number' => $table['table_number']],
                [
                    'branch_id' => $branchId,
                    'capacity' => $table['capacity'],
                    'status' => $table['status'],
                ]
            );
        }
    }
}
