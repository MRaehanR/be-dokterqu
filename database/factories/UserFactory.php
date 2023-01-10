<?php

namespace Database\Factories;

use App\Models\ApotekInfo;
use App\Models\DoctorInfo;
use App\Models\DoctorType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $this->faker = Faker::create('id_ID');

        $gender = [
            'm',
            'f',
        ];

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'op[kl;m,.',
            'phone' => '08' . $this->faker->randomNumber(9, true),
            'gender' => $gender[rand(0, 1)],
            'active' => rand(0, 1),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function roleCustomer()
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('customer');
        });
    }

    public function roleDoctor()
    {
        return $this->afterCreating(function (User $user) {
            $status = [
                'open',
                'accepted',
                'rejected',
            ];

            $typeDoctorCount = count(DoctorType::all());

            $doctorInfo = DoctorInfo::create([
                'user_id' => $user->id,
                'type_doctor_id' => rand(1, $typeDoctorCount),
                'experience' => rand(1, 10),
                'alumnus' => 'universitas indonesia',
                'alumnus_tahun' => '20' . rand(10, 22),
                'tempat_praktik' => 'rs. budi utomo',
                'status' => $status[rand(0, 2)],
                'cv' => 'images/default/default_photo_profile.png',
                'str' => 'images/default/default_photo_profile.png',
                'ktp' => 'images/default/default_photo_profile.png',
            ]);
            if ($doctorInfo->status == 'open' || $doctorInfo->status == 'rejected') {
                $user->update([
                    'active' => 0,
                ]);
            } else {
                $user->update([
                    'active' => 1,
                ]);
            }
            $user->assignRole('doctor');
        });
    }

    public function roleApotekOwner()
    {
        return $this->afterCreating(function (User $user) {
            $this->faker = Faker::create('id_ID');

            $status = [
                'open',
                'accepted',
                'rejected',
            ];

            $apotekInfo = ApotekInfo::create([
                'user_id' => $user->id,
                'name' => $this->faker->company(),
                'address' => $this->faker->address(),
                'ktp' => 'images/default/default_photo_profile.png',
                'npwp' => 'images/default/default_photo_profile.png',
                'surat_izin_usaha' => 'images/default/default_photo_profile.png',
                'image' => 'images/default/default_photo_profile.png',
                'latitude' => $this->faker->latitude(-7, -6.9),
                'longitude' => $this->faker->longitude(110.4, 110.6),
                'status' => $status[rand(0, 2)],
            ]);
            if ($apotekInfo->status == 'open' || $apotekInfo->status == 'rejected') {
                $user->update([
                    'active' => 0,
                ]);
            } else {
                $user->update([
                    'active' => 1,
                ]);
            }
            $user->assignRole('apotek_owner');
        });
    }
}
