<?php

namespace App\Services\User;

use App\Models\ApotekInfo;
use App\Models\DoctorInfo;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function App\Helpers\storeTo;

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
            'cv' => storeTo('private', 'cv', $data['cv'], $user->id),
            'str' => storeTo('private', 'str', $data['str'], $user->id),
            'ktp' => storeTo('private', 'ktp', $data['ktp'], $user->id),
        ]);

        $user->assignRole(User::TYPE_DOCTOR);

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
            'ktp' => storeTo('private', 'ktp', $data['ktp'], $user->id),
            'npwp' => storeTo('private', 'npwp', $data['npwp'], $user->id),
            'surat_izin_usaha' => storeTo('private', 'surat_izin_usaha', $data['surat_izin_usaha'], $user->id),
            'image' => $data['file']('image'),
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        $user->assignRole(User::TYPE_APOTEK_OWNER);

        return $apotekInfo;
    }

    private function createCustomer(User $user)
    {
        $user->update([
            'active' => true,
        ]);
        
        $user->assignRole(User::TYPE_CUSTOMER);

        event(new Registered($user));

        return $user;
    }
}
