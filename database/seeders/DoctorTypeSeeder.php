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
        DoctorType::create(['name' => 'mata']);
        DoctorType::create(['name' => 'telinga']);
        DoctorType::create(['name' => 'kulit']);
    }
}
