<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\DoctorInfo;
use App\Models\DoctorType;
use App\Models\OperationalTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isNull;

class DoctorController extends Controller
{
    public function getDoctorTypes(Request $request)
    {
        try {
            $data = [];

            if ($request->search) {
                $doctorTypes = DoctorType::where('name', 'like', "%$request->search%")->get();
            } else {
                $doctorTypes = DoctorType::get();
            }

            foreach ($doctorTypes as $doctorType) {
                array_push($data, [
                    'id' => $doctorType->id,
                    'name' => ucwords($doctorType->name),
                    'slug' => $doctorType->slug,
                    'links' => [
                        'doctors' => '/user/doctors?type=' . urlencode($doctorType->slug),
                    ]
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get all doctor types success',
                'data' => $data,
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

            $doctors = DoctorInfo::with(['user'])->status($request->input('status', 'accepted'));

            if (isset($request->search)) {
                $doctors = $doctors->whereHas('user', function ($query) use ($request) {
                    $query->where('name', 'like', "%$request->search%");
                });
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

            if ($request->input('nearby', false)) {
                $nearbyDoctors = [];
                $userAddress = (Auth('sanctum')->user())
                    ? CustomerAddress::where('user_id', Auth('sanctum')->user()->id)->where('default', true)->first() : null;
                if (!$userAddress) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User Address Not Found',
                    ], Response::HTTP_NOT_FOUND);
                }
                foreach ($doctors as $doctor) {
                    $distance = $this->calculateDistance($userAddress->latitude, $doctor->latitude, $userAddress->longitude, $doctor->longitude);
                    $doctor['distance'] = $distance;
                    array_push($nearbyDoctors, $doctor);
                }
                usort($nearbyDoctors, function ($a, $b) {
                    return $a->distance <=> $b->distance;
                });
            }

            foreach ($nearbyDoctors ?? $doctors as $doctor) {
                array_push($data, [
                    'name' => ucwords($doctor->user->name),
                    'slug' => $doctor->slug,
                    'photo' => $doctor->user->photo,
                    'type' => $doctor->doctorType->name,
                    'pengalaman' => $doctor->experience . ' Tahun',
                    'tempat_praktik' => ucwords($doctor->tempat_praktik),
                    'alumnus' => ucwords($doctor->alumnus),
                    'distance' => $doctor->distance ? round($doctor->distance, 2) . ' km' : null,
                    'is_online' => $doctor->user->is_online,
                    'price_homecare' => $doctor->price_homecare ? 'Rp. '
                        . number_format($doctor->price_homecare, 0, null, '.')
                        . ',00' : 'Rp. 0',
                    'price_homecare_int' => (int) $doctor->price_homecare,
                    'links' => [
                        'self' => '/user/doctor/' . urlencode($doctor->slug),
                        'type' => '/user/doctors?type=' . urlencode($doctor->doctorType->slug),
                    ]
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get all doctors success',
                'data' => [
                    'current' => $doctors->currentPage(),
                    'next_page' => (isset($nextPageUrl) && $doctors->nextPageUrl())
                        ? $doctors->nextPageUrl() . $nextPageUrl
                        : $doctors->nextPageUrl(),
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

    private function calculateDistance($lat1, $lat2, $lon1, $lon2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    public function getDoctorBySlug($slug)
    {
        try {
            $doctor = DoctorInfo::where('slug', $slug)->first();

            if (!$doctor) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Not Found',
                    'data' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get doctor detail success',
                'data' => [
                    'doctor_info_id' => $doctor->id,
                    'name' => ucwords($doctor->user->name),
                    'slug' => $doctor->slug,
                    'photo' => $doctor->user->photo,
                    'type' => $doctor->doctorType->name,
                    'pengalaman' => $doctor->experience . ' Tahun',
                    'alumnus' => ucwords($doctor->alumnus),
                    'tempat_praktik' => ucwords($doctor->tempat_praktik),
                    'price_homecare' => $doctor->price_homecare ? 'Rp. '
                        . number_format($doctor->price_homecare, 0, null, '.')
                        . ',00' : 'Rp. 0',
                    'price_homecare_int' => (int) $doctor->price_homecare,
                    'is_online' => $doctor->user->is_online,
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

    public function getOperationalTime(Request $request)
    {
        try {
            $data = [];
            $operationalTimes = OperationalTime::where('user_id', $request->user_id)->get();

            if (!$operationalTimes) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Not Found',
                    'data' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            foreach ($operationalTimes as $operationalTime) {
                $dayName = ucwords($operationalTime->day);
                $dayNumber = date('w', strtotime($dayName));
                $data[$dayNumber]['day'] = $dayName;
                $data[$dayNumber]['day_number'] = (int) $dayNumber;
                $data[$dayNumber]['date'] = date('Y-m-d', strtotime($dayName));
                $data[$dayNumber]['times'][] = [
                    'id' => $operationalTime->id,
                    'time' => substr($operationalTime->start_time, 0, 5),
                    'is_available' => $operationalTime->is_available,
                ];
            }
            ksort($data);

            return response()->json([
                'status' => true,
                'message' => 'Get doctor operational times success',
                'data' => $data,
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
