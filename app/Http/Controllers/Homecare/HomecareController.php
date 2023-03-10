<?php

namespace App\Http\Controllers\Homecare;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\DoctorInfo;
use App\Models\OperationalTime;
use App\Models\OrderDetail;
use App\Models\OrderHomecare;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HomecareController extends Controller
{
    public function setCheckout(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'address_id' => 'required',
                    'operational_time_id' => 'required',
                    'date' => 'required',
                    'voucher_id' => 'nullable',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $userAddress = CustomerAddress::where('id', $request->address_id)->first();

            if (!$userAddress) {
                return response()->json([
                    'status' => false,
                    'message' => 'User Address Not Found',
                ], Response::HTTP_NOT_FOUND);
            }

            $orderID = 'HOMECARE_' . Carbon::now()->format('YmdHis') . '_' . Auth::user()->id;
            $operationalTime = OperationalTime::where('id', $request->operational_time_id)->first();

            if(!$operationalTime) {
                return response()->json([
                    'status' => false,
                    'message' => 'Operational time not found',
                ], Response::HTTP_NOT_FOUND);
            }

            if (!$operationalTime->is_available) {
                return response()->json([
                    'status' => false,
                    'message' => 'Doctor Not Available',
                ], Response::HTTP_NOT_FOUND);
            }

            // Order Details
            $orderDetail = OrderDetail::create([
                'id' => $orderID,
                'user_id' => Auth::user()->id,
                'address_id' => $userAddress->id,
                'order_amount' => $operationalTime->user->doctorInfo->price_homecare,
            ]);

            if (isset($request->voucher_id)) {
                $orderDetail['voucher_id'] = $request->voucher_id;
            }

            // Order Homecare
            OrderHomecare::create([
                'order_detail_id' => $orderID,
                'doctor_info_id' => $operationalTime->user->doctorInfo->id,
                'operational_time_id' => $operationalTime->id,
                'date' => $request->date,
                'status' => 'waiting_payment',
            ]);

            // Update is_available in Operational Time
            $operationalTime->update([
                'is_available' => false,
            ]);

            $midtrans = $this->getMidtransSnapToken($orderID, $userAddress, $operationalTime->user->doctorInfo);

            return response()->json([
                'status' => true,
                'message' => 'Set checkout product success',
                'data' => $midtrans,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getMidtransSnapToken($order_id, CustomerAddress $userAddress, DoctorInfo $doctor)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = false;

        try {
            $params = [
                'transaction_details' => [
                    'order_id' => '',
                    'gross_amount' => 0,
                ],
                'item_details' => [],
                'customer_details' => [],
                'enabled_payments' => [
                    'gopay',
                    'shopeepay',
                    'bca_va',
                    'bri_va',
                    'bni_va',
                    'permata_va'
                ],
            ];

            $params['transaction_details']['order_id'] = $order_id;
            $params['customer_details'] = [
                'first_name' => $userAddress->user->name,
                'email' => $userAddress->user->email,
                'phone' => $userAddress->user->phone,
                'billing_address' => [
                    'first_name' => $userAddress->user->name,
                    'email' => $userAddress->user->email,
                    'phone' => $userAddress->user->phone,
                    'address' => $userAddress->address,
                ],
                'shipping_address' => [
                    'first_name' => $userAddress->recipient,
                    'phone' => $userAddress->phone,
                    'address' => $userAddress->address,
                    'location' => $userAddress->city_name . ', ' . $userAddress->province_name,
                ],
            ];
            $params['transaction_details']['gross_amount'] += (int) $doctor->price_homecare;
            array_push($params['item_details'], [
                'id' => $this->getFirstChar($doctor->user->name),
                'price' => $doctor->price_homecare,
                'quantity' => 1,
                'name' => 'Homecare Fee',
            ]);


            $params['transaction_details']['gross_amount'] += 2000;
            array_push($params['item_details'], [
                'id' => 'FEE-01',
                'price' => 2000,
                'quantity' => 1,
                'name' => 'Application Fee',
            ]);

            return [
                'token' => \Midtrans\Snap::getSnapToken($params),
                'url' => \Midtrans\Snap::getSnapUrl($params),
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')');
        }
    }

    private function getFirstChar($word)
    {
        $words = preg_split("/[\s,_-]+/", $word);
        $acronym = '';
        foreach ($words as $word) {
            $acronym .= mb_substr($word, 0, 1);
        }

        return $acronym;
    }
}
