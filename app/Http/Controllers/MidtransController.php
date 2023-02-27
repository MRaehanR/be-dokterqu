<?php

namespace App\Http\Controllers;

use App\Models\ApotekStock;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function notificationHandler(Request $request)
    {
        try {
            $type = strtolower(substr($request->order_id, 0, strpos($request->order_id, '_')));
            if ($type === 'shop') {
                return $this->shopHandler($request);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function shopHandler($request)
    {
        try {
            $orderPayment = OrderPayment::where('order_detail_id', $request->order_id)->first();

            if (!$orderPayment) {
                $orderPayment = new OrderPayment([
                    'order_detail_id' => $request->order_id,
                    'transaction_id' => $request->transaction_id,
                    'status' => $request->transaction_status,
                    'status_code' => $request->status_code,
                    'payment_type' => $request->payment_type,
                    'payment_amount' => $request->gross_amount,
                    'json_data' => json_encode($request->all()),
                ]);
                $orderPayment->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Transaction has been created',
                ], Response::HTTP_CREATED);
            }

            $signatureKey = $orderPayment->order_detail_id . $request->status_code . $orderPayment->payment_amount . env('MIDTRANS_SERVER_KEY');
            $signatureKey = hash('SHA512', $signatureKey);

            if ($signatureKey != $request->signature_key) {
                return response()->json([
                    'status' => false,
                    'message' => 'Signature is Invalid',
                ], Response::HTTP_FORBIDDEN);
            }

            $orderPayment->status = $request->transaction_status;
            $orderPayment->status_code = $request->status_code;
            if ($request->transaction_status == 'settlement') {
                $orderPayment->settlement_time = $request->transaction_time;
            }
            if ($request->transaction_status == 'cancel' || $request->transaction_status == 'expire' || $request->transaction_status == 'deny') {
                $orderItems = OrderItem::where('order_detail_id', $orderPayment->order_detail_id)->get();

                foreach ($orderItems as $orderItem) {
                    $apotekStock = ApotekStock::where('id', $orderItem->apotek_stock_id)->first();
                    $apotekStock->quantity += $orderItem->quantity;
                    $apotekStock->save();
                }
            }
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
