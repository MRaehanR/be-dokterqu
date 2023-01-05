<?php

namespace Database\Seeders;

use App\Models\DoctorType;
use Illuminate\Database\Seeder;

class DoctorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DoctorType::create(['name' => 'Sp. mata']);
        DoctorType::create(['name' => 'Sp. THT']);
        DoctorType::create(['name' => 'Sp. kulit & kelamin']);
        DoctorType::create(['name' => 'dokter gigi']);
        DoctorType::create(['name' => 'Sp. konservasi gigi']);
    }
}
