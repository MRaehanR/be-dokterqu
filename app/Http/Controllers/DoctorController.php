<?php

namespace App\Http\Controllers;

use App\Models\DoctorType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DoctorController extends Controller
{
    public function getDoctorTypes(Request $request)
    {
        try {
            if ($request->search) {
                $doctorType = DoctorType::where('prov_name', 'like', "%$request->search%")->get();
            } else {
                $doctorType = DoctorType::get();
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

    public function getDoctors(Request $request)
    {
        try {
            $data = [];
            $nextPageUrl = '';

            $doctors = User::with(['doctorInfo'])->whereHas('doctorInfo', function ($query) use ($request) {
                $query->status($request->input('status', 'accepted'));
            })->where('active', $request->input('is_account_active', true));
            if (isset($request->search)) {
                $doctors = $doctors->where('name', 'like', "%$request->search%");
                $nextPageUrl .= '&search=' . urlencode($request->search);
            }
            if (isset($request->type)) {
                $doctors = $doctors->doctorType($request->type);
                $nextPageUrl .= '&type=' . urlencode($request->type);
            }
            $doctors = $doctors->latest()->simplePaginate(10);

            if (count($doctors) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No data found',
                    'data' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            foreach ($doctors as $doctor) {
                array_push($data, [
                    'name' => ucwords($doctor->name),
                    'slug' => $doctor->doctorInfo->slug,
                    'photo' => $doctor->photo,
                    'type' => $doctor->doctorInfo->doctorType->name,
                    'pengalaman' => $doctor->doctorInfo->experience . ' Tahun',
                    'tempat_praktik' => ucwords($doctor->doctorInfo->tempat_praktik),
                    'alumnus' => ucwords($doctor->doctorInfo->alumnus),
                    'price_homecare' => $doctor->doctorInfo->price_homecare,
                    'price_homecare_int' => $doctor->doctorInfo->price_homecare_int,
                    'links' => [
                        'self' => '/user/doctor/' . urlencode($doctor->doctorInfo->slug),
                    ]
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get all products success',
                'data' => [
                    'current' => $doctors->currentPage(),
                    'next_page' => (isset($nextPageUrl) && $doctors->nextPageUrl()) ? $doctors->nextPageUrl() . $nextPageUrl : $doctors->nextPageUrl(),
                    'doctors' => $data,
                ],
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')');
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDoctorBySlug($slug)
    {
        try {
            $doctor = User::whereHas('doctorInfo', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })->first();

            if (!$doctor) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Not Found',
                    'data' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get product success',
                'data' => [
                    'doctor_info_id' => $doctor->doctorInfo->id,
                    'name' => ucwords($doctor->name),
                    'slug' => $doctor->doctorInfo->slug,
                    'photo' => $doctor->photo,
                    'type' => $doctor->doctorInfo->doctorType->name,
                    'pengalaman' => $doctor->doctorInfo->experience . ' Tahun',
                    'tempat_praktik' => ucwords($doctor->doctorInfo->tempat_praktik),
                    'price_homecare' => $doctor->doctorInfo->price_homecare,
                    'price_homecare_int' => $doctor->doctorInfo->price_homecare_int,
                    'is_online' => $doctor->is_online,
                ],
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')');
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
