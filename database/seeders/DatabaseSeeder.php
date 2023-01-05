<?php

namespace Database\Seeders;

use App\Models\ArticlePost;
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
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            DoctorTypeSeeder::class,
        ]);

        User::factory()->roleCustomer()->unverified()->count(10)->create();
        User::factory()->roleDoctor()->unverified()->count(10)->create();
        User::factory()->roleApotekOwner()->unverified()->count(10)->create();
    }
}
