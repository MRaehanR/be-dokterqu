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
}
