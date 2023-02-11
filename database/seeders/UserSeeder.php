<?php

namespace Database\Seeders;

use App\Models\ApotekInfo;
use App\Models\DoctorInfo;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userAdmin = User::create([
            'name' => 'Superadmin',
            'email' => 'superadmin@dokterqu.com',
            'password' => 'op[kl;m,.',
            'phone' => '085718127381',
            'gender' => 'm',
            'active' => 1,
        ]);
        $userAdmin->assignRole('superadmin');


        $userCust = User::create([
            'name' => 'Customer',
            'email' => 'customer@dokterqu.com',
            'password' => 'op[kl;m,.',
            'phone' => '085718127381',
            'gender' => 'f',
            'active' => 1,
        ]);
        $userCust->assignRole('customer');


        $userDokter = User::create([
            'name' => 'Dokter',
            'email' => 'dokter@dokterqu.com',
            'password' => 'op[kl;m,.',
            'phone' => '085718127382',
            'gender' => 'f',
            'active' => 1,
        ]);
        DoctorInfo::create([
            'user_id' => $userDokter->id,
            'type_doctor_id' => 1,
            'experience' => 5,
            'alumnus' => 'universitas indonesia',
            'alumnus_tahun' => 2019,
            'tempat_praktik' => 'rs. budi utomo',
            'status' => 'accepted',
            'cv' => 'assets/images/default/default_photo_profile.png',
            'str' => 'assets/images/default/default_photo_profile.png',
            'ktp' => 'assets/images/default/default_photo_profile.png',
        ]);
        $userDokter->assignRole('doctor');


        $userApotek = User::create([
            'name' => 'Apotek Owner',
            'email' => 'apotek@dokterqu.com',
            'password' => 'op[kl;m,.',
            'phone' => '085718127383',
            'gender' => 'm',
            'active' => 1,
        ]);
        ApotekInfo::create([
            'user_id' => $userApotek->id,
            'province_id' => 13,
            'city_id' => 198,
            'name' => 'Apotek Berkah Jaya',
            'address' => 'Jl. Soeharto No. 98, Jakarta.',
            'ktp' => 'assets/images/default/default_photo_profile.png',
            'npwp' => 'assets/images/default/default_photo_profile.png',
            'surat_izin_usaha' => 'assets/images/default/default_photo_profile.png',
            'latitude' => '-6.753476617844531',
            'longitude' => '110.84284069735776',
            'status' => 'accepted',
        ]);
        $userApotek->assignRole('apotek_owner');
    }
}
