<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormController extends Controller
{
    public function register()
    {
        return response()->json([
            [
                'label' => 'Name',
                'name' => 'name',
                'type' => 'text',
                'required' => true,
                'prepend_inner_icon' => 'mdi-account-circle',
            ],
            [
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
                'required' => true,
                'prepend_inner_icon' => 'mdi-email',
            ],
            [
                'label' => 'Password',
                'name' => 'password',
                'type' => 'password',
                'required' => true,
            ],
            [
                'label' => 'Confirmation Password',
                'name' => 'password_confirmation',
                'type' => 'password',
                'required' => true,
            ],
            [
                'label' => 'Provinsi',
                'name' => 'province_id',
                'type' => 'select',
                'required' => true,
                'options' => [],
            ],
            [
                'label' => 'Kota',
                'name' => 'city_id',
                'type' => 'select',
                'required' => true,
                'options' => [],
            ],
            [
                'label' => 'Phone',
                'name' => 'phone',
                'type' => 'phone',
                'required' => true,
                'prepend_inner_icon' => 'mdi-phone',
            ],
            [
                'label' => 'Photo Profile',
                'name' => 'photo',
                'type' => 'file',
                'required' => false,
            ],
            [
                'label' => 'Gender',
                'name' => 'gender',
                'type' => 'select',
                'required' => true,
                'options' => [
                    [
                        'label' => 'Male',
                        'value' => 'm',
                    ],
                    [
                        'label' => 'Female',
                        'value' => 'f',
                    ],
                ]
            ],
            [
                'label' => 'Role',
                'name' => 'role',
                'type' => 'hidden',
                'required' => true,
                'value' => 3
            ],
        ]);
    }

    public function registerDoctor()
    {
        return response()->json([
            [
                'label' => 'Curriculum Vitae',
                'name' => 'cv',
                'type' => 'file',
                'required' => true,
                'prepend_inner_icon' => 'mdi-paperclip',
            ],
            [
                'label' => 'Surat Tanda Registrasi',
                'name' => 'str',
                'type' => 'file',
                'required' => true,
                'prepend_inner_icon' => 'mdi-paperclip',
            ],
            [
                'label' => 'Kartu Tanda Penduduk',
                'name' => 'ktp',
                'type' => 'file',
                'required' => true,
                'prepend_inner_icon' => 'mdi-paperclip',
            ],
            [
                'label' => 'Jenis Dokter',
                'name' => 'type_doctor_id',
                'type' => 'select',
                'required' => true,
                'options' => [
                    [
                        'label' => 'Sp. Mata',
                        'value' => 1,
                    ],
                    [
                        'label' => 'Sp. THT',
                        'value' => 2,
                    ],
                    [
                        'label' => 'Sp. kulit & kelamin',
                        'value' => 3,
                    ],
                    [
                        'label' => 'Dokter Gigi',
                        'value' => 4,
                    ],
                    [
                        'label' => 'Sp. Konservasi Gigi',
                        'value' => 5,
                    ],
                ]
            ],
            [
                'label' => 'Pengalaman',
                'name' => 'experience',
                'type' => 'number',
                'required' => true,
                'prepend_inner_icon' => 'mdi-timer-outline',
            ],
            [
                'label' => 'Alumnus',
                'name' => 'alumnus',
                'type' => 'string',
                'required' => true,
                'prepend_inner_icon' => 'mdi-school',
            ],
            [
                'label' => 'Tahun Lulus',
                'name' => 'alumnus_tahun',
                'type' => 'number',
                'required' => true,
                'prepend_inner_icon' => 'mdi-timer-outline',
            ],
            [
                'label' => 'Tempat Praktik',
                'name' => 'tempat_praktik',
                'type' => 'string',
                'required' => true,
                'prepend_inner_icon' => 'mdi-hospital-building',
            ],
        ]);
    }

    public function registerApotek()
    {
        return response()->json([
            [
                'label' => 'Apotek Images',
                'name' => 'image[]',
                'type' => 'file',
                'required' => true,
                'prepend_inner_icon' => 'mdi-paperclip',
            ],
            [
                'label' => 'Alamat',
                'name' => 'address',
                'type' => 'text',
                'required' => true,
                'prepend_inner_icon' => 'mdi-home',
            ],
            [
                'label' => 'Kartu Tanda Penduduk',
                'name' => 'ktp',
                'type' => 'file',
                'required' => true,
                'prepend_inner_icon' => 'mdi-card-account-details',
            ],
            [
                'label' => 'Nomor Pokok Wajib Pajak',
                'name' => 'npwp',
                'type' => 'file',
                'required' => true,
                'prepend_inner_icon' => 'mdi-id-card',
            ],
            [
                'label' => 'Surat Izin Usaha',
                'name' => 'surat_izin_usaha',
                'type' => 'file',
                'required' => true,
                'prepend_inner_icon' => 'mdi-email',
            ],
            [
                'label' => 'Latitude Lokasi Apotek',
                'name' => 'latitude',
                'type' => 'text',
                'required' => true,
                'prepend_inner_icon' => 'mdi-latitude',
            ],
            [
                'label' => 'Longitude Lokasi Apotek',
                'name' => 'longitude',
                'type' => 'text',
                'required' => true,
                'prepend_inner_icon' => 'mdi-longitude',
            ],
        ]);
    }

    public function getDoctorTypes(Request $request)
    {
        try {
            if ($request->search) {
                $doctorType = DB::table('doctor_type')->where('prov_name', 'like', "%$request->search%")->get();
            } else {
                $doctorType = DB::table('doctor_type')->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Get all province success',
                'data' => $doctorType,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
