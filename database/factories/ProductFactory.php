<?php

namespace Database\Factories;

use App\Models\ApotekInfo;
use App\Models\ApotekStock;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'category_id' => rand(1, ProductCategory::all()->count()),
            'name' => $this->faker->sentence(),
            'slug' => $this->faker->slug(),
            'desc' => $this->faker->paragraph(),
        ];
    }

    public function addApotekStocks()
    {
        return $this->afterCreating(function() {
            ApotekStock::create([
                'apotek_info_id' => rand(1, ApotekInfo::all()->count()),
                'product_id' => rand(1, Product::all()->count()),
                'quantity' => rand(1, 100),
                'price' => $this->faker->numberBetween(10000, 50000),
            ]);
        });
    }
}
