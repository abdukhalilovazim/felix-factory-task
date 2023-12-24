<?php

namespace Database\Seeders;

use App\Models\ProductMaterial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductMaterial::factory(10)->create();
    }
}
