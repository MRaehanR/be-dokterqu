<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ApotekInfo;
use App\Models\OrderDetail;
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
                    'order_amount' => $orderDetail->order_amount,
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
}
