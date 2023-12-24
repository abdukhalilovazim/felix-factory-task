<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'material_id' => \App\Models\Material::all()->random()->id,
            'remainder' => $this->faker->numberBetween(10, 100),
            'price' => $this->faker->numberBetween(500, 2000),
        ];
    }
}
