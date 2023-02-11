<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CustomerAddressController extends Controller
{
    public function setAddress(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'label' => 'required|string|max:20',
                'address' => 'required|string|max:200',
                'recipient' => 'required|string|max:20',
                'phone' => 'required|max:15',
                'latitude' => 'required',
                'longitude' => 'required',
                'province_id' => 'required|exists:provinces,prov_id',
                'city_id' => 'required|exists:cities,city_id',
                'default' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $customerAddress = new CustomerAddress([
                'user_id' => Auth::user()->id,
                'label' => ucwords($request->label),
                'address' => ucwords($request->address),
                'recipient' => ucwords($request->recipient),
                'phone' => $request->phone,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'province_id' => $request->province_id,
                'city_id' => $request->city_id,
            ]);

            $oldDefaultAddress = CustomerAddress::where('user_id', Auth::user()->id)->where('default', 1)->first();
            if($request->default == 1 && isset($oldDefaultAddress)) {
                $oldDefaultAddress->update(['default' => 0]);
            }
            $customerAddress->default = $request->default;
            $customerAddress->save();

            return response()->json([
                'status' => true,
                'message' => 'Set Customer Address Success',
                'data' => [
                    'user_id' => $customerAddress->user_id,
                    'label' => $customerAddress->label,
                    'address' => $customerAddress->address,
                    'recipient' => $customerAddress->recipient,
                    'phone' => $customerAddress->phone,
                    'province_id' => $customerAddress->province_id,
                    'city_id' => $customerAddress->city_id,
                    'latitude' => $customerAddress->latitude,
                    'longitude' => $customerAddress->longitude,
                    'is_default' => $customerAddress->default,
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

    public function getAddresses(Request $request)
    {
        try {
            $data = [];

            if($request->default) {
                $addresses = CustomerAddress::where('user_id', Auth::user()->id)->where('default', 1)->get();    
            } else {
                $addresses = CustomerAddress::where('user_id', Auth::user()->id)->get();
            }

            if(count($addresses) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No data found',
                    'data' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            foreach ($addresses as $address) {
                array_push($data, [
                    'id' => $address->id,
                    'is_default' => $address->default,
                    'label' => $address->label,
                    'address' => $address->address,
                    'label' => $address->label,
                    'recipient' => $address->recipient,
                    'phone' => $address->phone,
                    'province' => $address->province_name,
                    'city' => $address->city_name,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get Customer Address Success',
                'data' => $data,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
