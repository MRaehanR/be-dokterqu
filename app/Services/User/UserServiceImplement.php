<?php

namespace App\Services\User;

use App\Models\ApotekInfo;
use App\Models\DoctorInfo;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function App\Helpers\storeImageToPublic;

class UserServiceImplement implements UserService
{
    public function createUser(array $data)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'photo' => $data['photo'],
                'phone' => $data['phone'],
                'gender' => $data['gender'],
            ]);

            switch ($data['role']) {
                case User::TYPE_DOCTOR:
                    $this->createDoctorInfo($data, $user);
                    break;

                case User::TYPE_APOTEK_OWNER:
                    $this->createApotekInfo($data, $user);
                    break;

                case User::TYPE_CUSTOMER:
                    $this->createCustomer($user);
                    break;
            }
            DB::commit();
            
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function createDoctorInfo(array $data, User $user)
    {
        $doctorInfo = DoctorInfo::create([
            'user_id' => $user->id,
            'slug' => Str::slug($user->name),
            'type_doctor_id' => $data['type_doctor_id'],
            'experience' => $data['experience'],
            'alumnus' => $data['alumnus'],
            'alumnus_tahun' => $data['alumnus_tahun'],
            'tempat_praktik' => $data['tempat_praktik'],
            'cv' => storeImageToPublic($data['cv'], 'cv'),
            'str' => storeImageToPublic($data['str'], 'str'),
            'ktp' => storeImageToPublic($data['ktp'], 'ktp'),
        ]);

        $user->assignRole('doctor');

        return $doctorInfo;
    }

    private function createApotekInfo(array $data, User $user)
    {
        $apotekInfo = ApotekInfo::create([
            'user_id' => $user->id,
            'province_id' => $data['province_id'],
            'city_id' => $data['city_id'],
            'name' => $data['name'],
            'address' => $data['address'],
            'ktp' => storeImageToPublic($data['ktp'], 'ktp'),
            'npwp' => storeImageToPublic($data['npwp'], 'npwp'),
            'surat_izin_usaha' => storeImageToPublic($data['surat_izin_usaha'], 'surat_izin_usaha'),
            'image' => $data['file']('image'),
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        $user->assignRole('apotek_owner');

        return $apotekInfo;
    }

    private function createCustomer(User $user)
    {
        $user->active = 1;
        $user->save();
        $user->assignRole('customer');

        event(new Registered($user));

        return $user;
    }
}
