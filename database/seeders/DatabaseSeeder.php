<?php

namespace Database\Seeders;

use App\Models\ApotekStock;
use App\Models\ArticlePost;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ProductCategorySeeder::class,
            RoleAndPermissionSeeder::class,
            DoctorTypeSeeder::class,
            ArticleCategorySeeder::class,
            UserSeeder::class,
        ]);

        User::factory()->roleCustomer()->unverified()->count(100)->create();
        User::factory()->roleDoctor()->unverified()->count(100)->create();
        User::factory()->roleApotekOwner()->unverified()->count(100)->create();
        ArticlePost::factory()->addComments()->addChildComments()->count(50)->create();
        Product::factory()->addApotekStocks()->count(100)->create();
    }
}
