<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


class ProductMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::all()->random()->id,
            'material_id' => \App\Models\Material::all()->random()->id,
            'quantity' => $this->faker->numberBetween(1, 10),
        ];
    }
}
