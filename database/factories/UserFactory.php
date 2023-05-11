<?php

namespace Database\Factories;

use App\Models\ApotekInfo;
use App\Models\CustomerAddress;
use App\Models\DoctorInfo;
use App\Models\DoctorType;
use App\Models\OperationalTime;
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

            $label = [
                'Rumah',
                'Kantor',
                'Sekolah',
            ];

            CustomerAddress::create([
                'user_id' => $user->id,
                'label' => $label[rand(0,2)],
                'address' => $this->faker->address,
                'note' => $this->faker->sentence(),
                'recipient' => $user->name,
                'phone' => $user->phone,
                'latitude' => $this->faker->latitude(-7, -6.9),
                'longitude' => $this->faker->longitude(110.4, 110.6),
                'province_id' => 13,
                'city_id' => 198,
                'default' => 1,
            ]);
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
                'address' => $this->faker->address(),
                'latitude' => $this->faker->latitude(-7, -6.9),
                'longitude' => $this->faker->longitude(110.4, 110.6),
                'price_homecare' => $this->faker->numberBetween(10000, 100000),
                // 'is_available' => rand(0, 1),
                'slug' => Str::slug($user->name),
                'cv' => 'assets/images/default/default_photo_profile.png',
                'str' => 'assets/images/default/default_photo_profile.png',
                'ktp' => 'assets/images/default/default_photo_profile.png',
            ]);

            for ($i=1; $i <= 7; $i++) { 
                OperationalTime::create([
                    'user_id' => $user->id,
                    'type' => 'homecare',
                    'day' => $i,
                    'start_time' => $this->faker->time('H:i:s'),
                    'is_available' => rand(0,1),
                ]);
            }
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
                'province_id' => 13,
                'city_id' => 198,
                'name' => $this->faker->company(),
                'address' => $this->faker->address(),
                'ktp' => 'assets/images/default/default_photo_profile.png',
                'npwp' => 'assets/images/default/default_photo_profile.png',
                'surat_izin_usaha' => 'assets/images/default/default_photo_profile.png',
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
