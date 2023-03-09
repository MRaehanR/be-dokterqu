<?php

namespace Database\Seeders;

use App\Models\DoctorType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DoctorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DoctorType::create(['name' => 'Sp. mata', 'slug' => 'mata']);
        DoctorType::create(['name' => 'Sp. THT', 'slug' => 'tht']);
        DoctorType::create(['name' => 'Sp. kulit & kelamin', 'slug' => 'kulit-kelamin']);
        DoctorType::create(['name' => 'dokter gigi', 'slug' => 'dokter-gigi']);
        DoctorType::create(['name' => 'Sp. konservasi gigi',  'slug' => 'konservasi-gigi']);
    }
}
