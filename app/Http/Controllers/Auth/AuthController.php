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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

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

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email or Password does not match.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user = User::where('email', $request->email)->first();
            $token = $user->createToken("API ACCESS TOKEN")->plainTextToken;
            $user->role = $user->getRoleNames()->first();
            $user->token = $token;

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'data' => $user,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
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
                    'name' => 'required|min:5',
                    'email' => 'required|email|unique:users',
                    'password' => 'required',
                    'photo' => 'mimes:jpg,png,jpeg,bmp|max:2048',
                    'phone' => 'required|unique:users',
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
                'name' => ucwords(strtolower($request->name)),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'photo' => $this->storeImage($request->file('photo'), 'photo_profile'),
                'phone' => $request->phone,
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
                    $doctorInfo = new DoctorInfo([
                        'user_id' => $user->id,
                        'type_doctor_id' => $request->type_doctor_id,
                        'experience' => $request->experience,
                        'alumnus' => ucwords(strtolower($request->alumnus)),
                        'alumnus_tahun' => $request->alumnus_tahun,
                        'tempat_praktik' => ucwords(strtolower($request->tempat_praktik)),
                        'cv' => $this->storeImage($request->file('cv'), 'cv'),
                        'str' => $this->storeImage($request->file('str'), 'str'),
                        'ktp' => $this->storeImage($request->file('ktp'), 'ktp'),
                    ]);
                    $user->assignRole('doctor');
                    event(new Registered($user));

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
                            'name' => 'required|min:5',
                            'address' => 'required',
                            'ktp' => 'required|mimes:jpg,png,jpeg,bmp|max:2048',
                            'npwp' => 'required|mimes:jpg,png,jpeg,bmp|max:2048',
                            'surat_izin_usaha' => 'required|mimes:jpg,png,jpeg,bmp|max:2048',
                            'image' => 'required|mimes:jpg,png,jpeg,bmp|max:2048',
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
                        'name' => ucwords(strtolower($request->name)),
                        'address' => $request->address,
                        'ktp' => $this->storeImage($request->file('ktp'), 'ktp'),
                        'npwp' => $this->storeImage($request->file('npwp'), 'npwp'),
                        'surat_izin_usaha' => $this->storeImage($request->file('surat_izin_usaha'), 'surat_izin_usaha'),
                        'image' => $this->storeImage($request->file('image'), 'apotek_image'),
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                    ]);
                    $user->assignRole('apotek_owner');
                    event(new Registered($user));

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
