<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProductCategory::create([
            'name' => 'mata',
            'slug' => 'mata',
        ]);

        ProductCategory::create([
            'name' => 'telinga',
            'slug' => 'telinga',
        ]);
    }
}
