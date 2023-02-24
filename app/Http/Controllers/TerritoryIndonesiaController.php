<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TerritoryIndonesiaController extends Controller
{
    public function getProvinces(Request $request)
    {
        try {
            if ($request->search) {
                $provinces = DB::table('provinces')->where('prov_name', 'like', "%$request->search%")->get();
            } else {
                $provinces = DB::table('provinces')->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Get all province success',
                'data' => $provinces,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getCities(Request $request)
    {
        try {
            if($request->search) {
                $cities = DB::table('cities')->where('city_name', 'like', "%$request->search%")->get();
            }
            if($request->province_id){
                $cities = DB::table('cities')->where('prov_id', $request->province_id)->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Get all cities success',
                'data' => $cities,
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
