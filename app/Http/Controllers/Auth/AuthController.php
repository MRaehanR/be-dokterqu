<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ApotekInfo;
use App\Models\DoctorInfo;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if(!User::where('email', $request->email)->first()){
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has not been registered.',
                ], Response::HTTP_NOT_FOUND);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email or Password does not match.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user->active) {
                return response()->json([
                    'status' => false,
                    'message' => ($user->roles->first()->name == 'doctor' || $user->roles->first()->name == 'apotek_owner') ? 'Your data has not been verified' : 'Your Account is Disabled',
                ], Response::HTTP_UNAUTHORIZED);
            }
            $token = $user->createToken("API ACCESS TOKEN")->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified' => $user->email_verified,
                    'photo' => $user->photo,
                    'role' => $user->roles->first()->name,
                    'token' => $token,
                ],
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'User Logout Successfully',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|min:5',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|min:8|confirmed',
                    'photo' => 'mimes:jpg,png,jpeg,bmp|max:2048',
                    'phone' => 'required|unique:users|max:15',
                    'gender' => 'required|in:m,f',
                    'role' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = new User([
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
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'type_doctor_id' => 'required',
                            'experience' => 'required',
                            'alumnus' => 'required',
                            'alumnus_tahun' => 'required|integer',
                            'tempat_praktik' => 'required',
                            'cv' => 'required|mimes:jpg,png,jpeg,bmp|max:2048',
                            'str' => 'required|mimes:jpg,png,jpeg,bmp|max:2048',
                            'ktp' => 'required|mimes:jpg,png,jpeg,bmp|max:2048',
                        ]
                    );

                    if ($validator->fails()) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Validation error',
                            'errors' => $validator->errors()
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    $user->save();
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
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'province_id' => 'required',
                            'city_id' => 'required',
                            'name' => 'required|string|min:5',
                            'address' => 'required|string|min:5',
                            'ktp' => 'required|mimes:jpg,png,jpeg,bmp|max:2048',
                            'npwp' => 'required|mimes:jpg,png,jpeg,bmp|max:2048',
                            'surat_izin_usaha' => 'required|mimes:jpg,png,jpeg,bmp|max:2048',
                            'image.*' => 'required|mimes:jpg,png,jpeg,bmp,webp|max:5000',
                            'image' => 'max:5',
                            'latitude' => 'required',
                            'longitude' => 'required',
                        ]
                    );

                    if ($validator->fails()) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Validation error',
                            'errors' => $validator->errors()
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    $user->save();
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

                    event(new Registered($user));

                    return response()->json([
                        'status' => true,
                        'message' => 'User created with role customer',
                        'data' => $user,
                    ], Response::HTTP_CREATED);
                    break;
            }
        } catch (\Throwable $th) {
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
