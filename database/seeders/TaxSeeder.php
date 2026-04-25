<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            ['name' => 'Sales Tax', 'percentage' => 5.00, 'tax_type' => 'line', 'calc_order' => 1],
            ['name' => 'Service Tax', 'percentage' => 8.50, 'tax_type' => 'line', 'calc_order' => 1],
            ['name' => 'VAT', 'percentage' => 15.00, 'tax_type' => 'line', 'calc_order' => 1],
            ['name' => 'Federal Tax A', 'percentage' => 4.00, 'tax_type' => 'invoice', 'calc_order' => 1],
            ['name' => 'Provincial Tax B', 'percentage' => 2.50, 'tax_type' => 'invoice', 'calc_order' => 2],
            ['name' => 'Withholding Adjustment', 'percentage' => 1.25, 'tax_type' => 'invoice', 'calc_order' => 3],
        ];

        Tax::query()->delete();

        foreach ($taxes as $tax) {
            Tax::create($tax);
        }
    }
}
