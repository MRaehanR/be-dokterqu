<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterDoctorRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\ApotekInfo;
use App\Models\DoctorInfo;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        $role = $user->roles->first()->name;

        if (!$user) {
            return response()->error('Account not found.', Response::HTTP_NOT_FOUND);
        }

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->error('Email or Password does not match.', Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->active) {
            return response()->error((in_array($role, [config('const.user_type.doctor'), config('const.user_type.apotek_owner')])) ? 'Your data has not been verified' : 'Your Account is Disabled', Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('access_token')->plainTextToken;

        return response()
            ->success(
                'User Logged In Successfully',
                Response::HTTP_CREATED,
                [
                    'user' => (new UserResource($user)),
                    'access_token' => $token,
                ]
            );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->success('User Logout Successfully', Response::HTTP_OK);
    }

    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'photo' => $request->file('photo'),
                'phone' => $request->phone,
                'gender' => $request->gender,
            ]);

            switch ($request->role) {
                    // Doctor
                case 1:
                    $doctorInfo = DoctorInfo::create([
                        'user_id' => $user->id,
                        'slug' => Str::slug($user->name),
                        'type_doctor_id' => $request->type_doctor_id,
                        'experience' => $request->experience,
                        'alumnus' => $request->alumnus,
                        'alumnus_tahun' => $request->alumnus_tahun,
                        'tempat_praktik' => $request->tempat_praktik,
                        'cv' => $this->storeImage($request->file('cv'), 'cv'),
                        'str' => $this->storeImage($request->file('str'), 'str'),
                        'ktp' => $this->storeImage($request->file('ktp'), 'ktp'),
                    ]);
                    $user->assignRole('doctor');

                    DB::commit();

                    return response()->json([
                        'status' => true,
                        'message' => 'User created with role doctor',
                        'data' => [
                            'user' => $user,
                            'doctor_info' => $doctorInfo
                        ],
                    ], Response::HTTP_CREATED);
                    break;

                    // Apotek Owner 
                case 2:
                    $apotekInfo = ApotekInfo::create([
                        'user_id' => $user->id,
                        'province_id' => $request->province_id,
                        'city_id' => $request->city_id,
                        'name' => $request->name,
                        'address' => $request->address,
                        'ktp' => $this->storeImage($request->file('ktp'), 'ktp'),
                        'npwp' => $this->storeImage($request->file('npwp'), 'npwp'),
                        'surat_izin_usaha' => $this->storeImage($request->file('surat_izin_usaha'), 'surat_izin_usaha'),
                        'image' => $request->file('image'),
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                    ]);
                    $user->assignRole('apotek_owner');

                    DB::commit();

                    return response()->json([
                        'status' => true,
                        'message' => 'User created with role apotek owner',
                        'data' => [
                            'user' => $user,
                            'apotek_info' => $apotekInfo
                        ],
                    ], Response::HTTP_CREATED);
                    break;

                    // Customer
                case 3:
                    $user->active = 1;
                    $user->save();
                    $user->assignRole('customer');

                    DB::commit();

                    event(new Registered($user));

                    return response()->json([
                        'status' => true,
                        'message' => 'User created with role customer',
                        'data' => $user,
                    ], Response::HTTP_CREATED);
                    break;
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function storeImage($file, String $path)
    {
        if ($file) {
            $fileName = Carbon::now()->format('YmdHis') . "_" . md5_file($file) . "." . $file->getClientOriginalExtension();
            $filePath = "storage/images/$path/" . $fileName;
            $file->storeAs(
                "public/images/$path",
                $fileName
            );
            return $filePath;
        }
        return null;
    }
}
