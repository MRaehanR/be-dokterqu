<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ApotekInfo;
use App\Models\OrderDetail;
use App\Models\OrderHomecare;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class HistoryPurchaseController extends Controller
{
    public function getHistoryShop(Request $request)
    {
        try {
            $data = [];
            $nextPageUrl = '';
            $orderDetails = OrderDetail::where('user_id', Auth()->user()->id)->with(['orderItems.apotekStock.product', 'orderItems.apotekStock.apotekInfo'])->has('orderItems')->has('orderPayment');

            if (isset($request->status)) {
                $orderDetails = $orderDetails->where('status', $request->status);
                $nextPageUrl .= '&status=' . urlencode($request->status);
            }
            $orderDetails = $orderDetails->latest()->simplePaginate(10);

            if (count($orderDetails) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Data Order Detail',
                ], Response::HTTP_NOT_FOUND);
            }

            foreach ($orderDetails as $orderDetail) {
                $orderItem = $orderDetail->orderItems[0];
                $apotekInfo = $orderItem->apotekStock->apotekInfo;
                $data[] = [
                    'id' => $orderDetail->id,
                    'order_amount' => "Rp. " . number_format($orderDetail->order_amount, 0, null, '.'),
                    'status' => $orderDetail->status,
                    'order_item_count' => count($orderDetail->orderItems),
                    'order_at' => date_format($orderDetail->created_at, 'd M Y'),
                    'apotek' => [
                        'name' => $apotekInfo->name,
                        'address' => $apotekInfo->address,
                        'image' => $apotekInfo->image,
                        'province_name' => $apotekInfo->province_name,
                        'city_name' => $apotekInfo->city_name,
                    ],
                    'product' => [
                        'name' => $orderItem->apotekStock->product->name,
                        'quantity' => $orderItem->quantity,
                        'image' => $orderItem->apotekStock->product->images,
                        'price' => "Rp. " . number_format($orderItem->apotekStock->price, 0, null, '.'),
                    ],
                    'links' => [
                        'self' => '/user/customer/history/shop/' . $orderDetail->id,
                        'cancel_order' => ($orderDetail->status === 'waiting_payment') ? '/user/customer/history/shop/' . $orderDetail->id . '/cancel' : null,
                    ]
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Get History Shop Success',
                'data' => [
                    'current' => $orderDetails->currentPage(),
                    'next_page' => (isset($nextPageUrl) && $orderDetails->nextPageUrl())
                        ? $orderDetails->nextPageUrl() . $nextPageUrl
                        : $orderDetails->nextPageUrl(),
                    'orders' => $data,
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

    public function getDetailHistoryShop($orderId)
    {
        try {
            $data = [];
            $orderDetail = OrderDetail::where('id', $orderId)->with(['orderItems.apotekStock.product', 'orderItems.apotekStock.apotekInfo', 'address', 'orderPayment'])->first();

            if (!$orderDetail) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Data Order Detail',
                ], Response::HTTP_NOT_FOUND);
            }

            $apotekInfo = $orderDetail->orderItems[0]->apotekStock->apotekInfo;
            $address = $orderDetail->address;
            $data = [
                'id' => $orderDetail->id,
                'order_amount' => "Rp. " . number_format($orderDetail->order_amount, 0, null, '.'),
                'status' => $orderDetail->status,
                'order_item_count' => count($orderDetail->orderItems),
                'order_at' => date_format($orderDetail->created_at, 'd M Y'),
                'payment_method' => $orderDetail->orderPayment->payment_type,
                'address' => [
                    'label' => $address->label,
                    'address' => $address->address,
                    'recipient' => $address->recipient,
                    'phone' => $address->phone,
                    'note' => $address->note,
                ],
                'apotek' => [
                    'name' => $apotekInfo->name,
                    'address' => $apotekInfo->address,
                    'image' => $apotekInfo->image,
                    'province_name' => $apotekInfo->province_name,
                    'city_name' => $apotekInfo->city_name,
                ],
            ];

            foreach ($orderDetail->orderItems as $orderItem) {
                $data['products'][] = [
                    'name' => $orderItem->apotekStock->product->name,
                    'quantity' => $orderItem->quantity,
                    'image' => $orderItem->apotekStock->product->images,
                    'price' => "Rp. " . number_format($orderItem->apotekStock->price, 0, null, '.'),
                ];
            }

            $data['link'] = [
                'cancel_order' => ($orderDetail->status === 'waiting_payment') ? '/user/customer/history/shop/' . $orderDetail->id . '/cancel' : null,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Get Detail History Shop Success',
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

    public function cancelHistoryShop($orderId)
    {
        try {
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = false;

            $orderDetail = OrderDetail::with('orderPayment')->where('id', $orderId)->first();

            if ($orderDetail->orderPayment->status === 'settlement' || $orderDetail->status === 'canceled' || $orderDetail->status === 'finish') {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot Cancel Order Shop',
                ], Response::HTTP_FORBIDDEN);
            }

            \Midtrans\Transaction::cancel($orderId);
            $orderDetail->update([
                'status' => 'canceled'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Cancel Order Shop Success',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')');
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getHistoryHomecare(Request $request)
    {
        try {
            $data = [];
            $nextPageUrl = '';
            $orderDetails = OrderDetail::where('user_id', Auth()->user()->id)->with(['orderHomecares.doctorInfo.user', 'orderHomecares.operationalTime'])->has('orderHomecares')->has('orderPayment');

            if (isset($request->status)) {
                $orderDetails = $orderDetails->whereHas('orderHomecares', function ($query) use ($request) {
                    $query->where('status', $request->status);
                });
                $nextPageUrl .= '&status=' . urlencode($request->status);
            }
            $orderDetails = $orderDetails->latest()->simplePaginate(10);

            if (count($orderDetails) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Data Order Detail',
                ], Response::HTTP_NOT_FOUND);
            }

            foreach ($orderDetails as $orderDetail) {
                $doctor = $orderDetail->orderHomecares->doctorInfo;
                $operationalTime = $orderDetail->orderHomecares->operationalTime;
                $data[] = [
                    'id' => $orderDetail->id,
                    'order_amount' => "Rp. " . number_format($orderDetail->order_amount, 0, null, '.'),
                    'status' => $orderDetail->orderHomecares->status,
                    'order_date' => $orderDetail->orderHomecares->date,
                    'order_at' => date_format($orderDetail->created_at, 'd M Y'),
                    'doctor' => [
                        'name' => ucwords($doctor->user->name),
                        'slug' => $doctor->slug,
                        'photo' => $doctor->user->photo,
                        'phone' => $doctor->user->phone,
                        'type' => $doctor->doctorType->name,
                        'pengalaman' => $doctor->experience . ' Tahun',
                        'tempat_praktik' => ucwords($doctor->tempat_praktik),
                        'alumnus' => ucwords($doctor->alumnus),
                        'is_online' => $doctor->user->is_online,
                        'price_homecare' => $doctor->price_homecare ? 'Rp. '
                            . number_format($doctor->price_homecare, 0, null, '.')
                            . ',00' : 'Rp. 0',
                        'price_homecare_int' => (int) $doctor->price_homecare,
                    ],
                    'operational_time' => [
                        'id' => $operationalTime->id,
                        'time' => substr($operationalTime->start_time, 0, 5),
                    ],
                    'links' => [
                        'cancel_order' => ($orderDetail->orderHomecares->status === 'waiting_payment') ? '/user/customer/history/homecare/' . $orderDetail->id . '/cancel' : null,
                    ]
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Get History Shop Success',
                'data' => [
                    'current' => $orderDetails->currentPage(),
                    'next_page' => (isset($nextPageUrl) && $orderDetails->nextPageUrl())
                        ? $orderDetails->nextPageUrl() . $nextPageUrl
                        : $orderDetails->nextPageUrl(),
                    'homecare' => $data,
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

    public function cancelHistoryHomecare($orderId)
    {
        try {
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = false;

            $orderDetail = OrderDetail::with(['orderPayment', 'orderHomecares'])->where('id', $orderId)->first();

            if ($orderDetail->orderPayment->status === 'settlement' || $orderDetail->orderHomecares->status === 'canceled' || $orderDetail->orderHomecares->status === 'finished') {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot Cancel Order Shop',
                ], Response::HTTP_FORBIDDEN);
            }

            \Midtrans\Transaction::cancel($orderId);
            $orderDetail->orderHomecares->update([
                'status' => 'canceled'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Cancel Order Homecare Success',
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
