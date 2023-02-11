<?php

namespace App\Http\Controllers;

use App\Models\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function notificationHandler(Request $request)
    {
        try {
            $orderPayment = OrderPayment::where('transaction_id', $request->transaction_id)->first();

            if(!$orderPayment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction not found',
                ], Response::HTTP_NOT_FOUND);
            }

            $signatureKey = $orderPayment->order_detail_id . $orderPayment->status_code . $orderPayment->payment_amount . env('MIDTRANS_SERVER_KEY');
            $signatureKey = hash('SHA512', $signatureKey);

            if ($signatureKey != $request->signature_key) {
                return response()->json([
                    'status' => false,
                    'message' => 'Signature is Invalid',
                ], Response::HTTP_FORBIDDEN);
            }

            $orderPayment->status = $request->transaction_status;
            $orderPayment->status_code = $request->status_code;
            $orderPayment->settlement_time = $request->settlement_time;
            $orderPayment->save();

            return response()->json([
                'status' => true,
                'message' => 'Transaction has been updated',
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
