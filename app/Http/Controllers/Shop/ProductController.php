<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ApotekInfo;
use App\Models\ApotekStock;
use App\Models\ApotekStockTransaction;
use App\Models\CartItem;
use App\Models\CustomerAddress;
use App\Models\OrderDetail;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function getAllProducts(Request $request)
    {
        try {
            $data = [];
            $nextPageUrl = '';

            $products = Product::with(['category', 'apotekStock'])->has('apotekStock');
            if (isset($request->search)) {
                $product = $products->whereHas('apotekStock', function ($query) use ($request) {
                    $query->where('name', 'like', "%$request->search%")->orWhere('desc', 'like', "%$request->search%");
                });
                $nextPageUrl .= '&search=' . urlencode($request->search);
            }
            if (isset($request->category)) {
                $products = $products->category($request->category);
                $nextPageUrl .= '&category=' . urlencode($request->category);
            }
            $products = $products->latest()->simplePaginate(10);

            if (count($products) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No data found',
                    'data' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            foreach ($products as $product) {
                if (isset(Auth('sanctum')->user()->id)) $cartItem = CartItem::where('user_id', Auth('sanctum')->user()->id)->where('product_id', $product->id)->first();
                array_push($data, [
                    'name' => ucwords($product->name),
                    'image' => $product->images,
                    'category' => ucwords($product->category->name),
                    'price' => $product->range_price,
                    'user' => [
                        'inCart' => (isset($cartItem->quantity)) ? $cartItem->quantity : 0,
                    ],
                    'links' => [
                        'self' => '/shop/product/' . urlencode($product->slug),
                        'category' => '/shop/products?category=' . urlencode($product->category->name),
                        'cart' => [
                            'add_cart' => '/shop/cart/add?product_id=' . $product->id,
                            'remove_cart' => '/shop/cart/remove?product_id=' . $product->id,
                        ]
                    ],
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get all products success',
                'data' => [
                    'current' => $products->currentPage(),
                    'next_page' => (isset($nextPageUrl) && $products->nextPageUrl()) ? $products->nextPageUrl() . $nextPageUrl : $products->nextPageUrl(),
                    'products' => $data,
                ],
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getProductBySlug($slug)
    {
        try {
            $product = Product::whereHas('apotekStock', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })->first();

            if (!$product) {
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
                    'name' => $product->name,
                    'desc' => $product->desc,
                    'image' => $product->images,
                    'category' => ucwords($product->category->name),
                    'price' => $product->range_price,
                    'links' => [
                        'category' => '/shop/products?category=' . urlencode($product->category->name),
                        'cart' => [
                            'add_cart' => '/shop/cart/add?product_id=' . $product->id,
                            'remove_cart' => '/shop/cart/remove?product_id=' . $product->id,
                            'update_cart' => '/shop/cart/update?product_id=' . $product->id . '&quantity=',
                        ]
                    ],
                ],
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getApotekHasProducts(Request $request)
    {
        try {
            $userAddress = CustomerAddress::where('id', $request->address_id)->first();
            $products = $request->products;

            if (!$userAddress) {
                return response()->json([
                    'status' => false,
                    'message' => 'User Address Not Found',
                ], Response::HTTP_NOT_FOUND);
            }

            $pharmacies = ApotekInfo::query();
            foreach ($products as $product) {
                $id = $product['product_id'];
                $quantity = $product['quantity'];
                $pharmacies = $pharmacies->whereHas('apotekStock', function ($query) use ($id, $quantity) {
                    $query->where('product_id', $id)->where('quantity', '>', $quantity);
                })->status('accepted');
            }
            $pharmacies = $pharmacies->get();

            $nearbyPharmacies = [];
            foreach ($pharmacies as $pharmacy) {
                $distance = $this->calculateDistance($userAddress->latitude, $pharmacy->latitude, $userAddress->longitude, $pharmacy->longitude);
                $pharmacy['distance'] = $distance;
                array_push($nearbyPharmacies, $pharmacy);
            }

            if (!$nearbyPharmacies) {
                return response()->json([
                    'status' => false,
                    'message' => 'Apotek available Not Found',
                ], Response::HTTP_NOT_FOUND);
            }

            usort($nearbyPharmacies, function ($a, $b) {
                return $a->distance <=> $b->distance;
            });
            $nearbyPharmacies = $nearbyPharmacies[0];

            $productApotekStockId = [];
            foreach ($products as $product) {
                $apotek_stock = ApotekStock::where('apotek_info_id', $nearbyPharmacies->id)->where('product_id', $product['product_id'])->first();
                $productApotekStockId[] = [
                    'apotek_stock_id' => $apotek_stock->id,
                    'quantity' => $product['quantity'],
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Get nearby apotek success',
                'data' => [
                    'apotek' => [
                        'name' => $nearbyPharmacies->name,
                        'address' => $nearbyPharmacies->address,
                        'image' => $nearbyPharmacies->image[0],
                        'distance' => round($nearbyPharmacies->distance, 2) . ' km',
                        'location' => $nearbyPharmacies->city_name . ', ' . $nearbyPharmacies->province_name,
                    ],
                    'products' => $productApotekStockId,
                ],
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
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

    public function getMidtransSnapToken(Request $request)
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
            ];

            $validator = Validator::make(
                $request->all(),
                [
                    'products' => 'required',
                    'address_id' => 'required',
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

            $user = User::where('id', Auth::user()->id)->first();
            $userAddress = CustomerAddress::where('id', $request->address_id)->first();


            if (!$userAddress) {
                return response()->json([
                    'status' => false,
                    'message' => 'User Address Not Found',
                ], Response::HTTP_NOT_FOUND);
            }

            $params['transaction_details']['order_id'] = 'SHOP_' . Carbon::now()->format('YmdHis') . '_' . $user->id;
            $params['customer_details'] = [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'billing_address' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $userAddress->address,
                ],
                'shipping_address' => [
                    'first_name' => $userAddress->recipient,
                    'phone' => $userAddress->phone,
                    'address' => $userAddress->address,
                    'location' => $userAddress->city_name . ', ' . $userAddress->province_name,
                ],
            ];


            foreach ($request->products as $productReq) {
                $apotekStock = ApotekStock::where('id', $productReq['apotek_stock_id'])->first();

                $params['transaction_details']['gross_amount'] += (int) $apotekStock->price * (int) $productReq['quantity'];

                array_push($params['item_details'], [
                    'id' => $this->getFirstChar($apotekStock->apotekInfo->name) . '-' . $apotekStock->id,
                    'price' => (int) $apotekStock->price,
                    'quantity' => $productReq['quantity'],
                    'name' => substr($apotekStock->product->name, 0, 50),
                ]);
            }


            $params['transaction_details']['gross_amount'] += 2000;
            array_push($params['item_details'], [
                'id' => 'FEE-01',
                'price' => 2000,
                'quantity' => 1,
                'name' => 'Application Fee',
            ]);

            $snapToken = [
                'token' => \Midtrans\Snap::getSnapToken($params),
                'url' => \Midtrans\Snap::getSnapUrl($params),
            ];

            return response()->json([
                'status' => true,
                'message' => 'Generate midtrans snap token success',
                'data' => $snapToken,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function setCheckoutProduct(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'products' => 'required',
                    'order_id' => 'required',
                    'gross_amount' => 'required',
                    'transaction_id' => 'required',
                    'voucher_id' => 'nullable',
                    'shipping_costs' => 'nullable',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $orderDetail = OrderDetail::where('id', $request->order_id)->first();
            if($orderDetail) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order ID already exist, Duplicate Entry',
                ], Response::HTTP_FORBIDDEN);
            }

            // Order Details
            $orderDetail = new OrderDetail([
                'id' => $request->order_id,
                'user_id' => Auth::user()->id,
                'order_amount' => $request->gross_amount,
            ]);

            if (isset($request->voucher_id)) {
                $orderDetail['voucher_id'] = $request->voucher_id;
            }

            if (isset($request->shipping_costs)) {
                $orderDetail['shipping_costs'] = $request->shipping_costs;
            }
            $orderDetail->save();

            // Order Items
            $orderItem = [];
            $stockTransaction = [];

            foreach ($request->products as $product) {
                array_push($orderItem, [
                    'order_detail_id' => $request->order_id,
                    'apotek_stock_id' => $product['apotek_stock_id'],
                    'quantity' => $product['quantity'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                array_push($stockTransaction, [
                    'apotek_stock_id' => $product['apotek_stock_id'],
                    'type' => 'out',
                    'quantity' => $product['quantity'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $apotekStock = ApotekStock::where('id', $product['apotek_stock_id'])->first();
                $apotekStock->update([
                    'quantity' => $apotekStock->quantity - $product['quantity'],
                ]);
            }

            DB::table('order_items')->insert($orderItem);

            // Stock Transactions
            DB::table('apotek_stock_transactions')->insert($stockTransaction);

            // Order Payments
            $orderPayment = new OrderPayment([
                'order_detail_id' => $request->order_id,
                'transaction_id' => $request->transaction_id,
                'status' => $request->transaction_status,
                'status_code' => $request->status_code,
                'payment_type' => $request->payment_type,
                'payment_amount' => $request->gross_amount,
                'json_data' => json_encode($request->all()),
            ]);

            if ($request->transaction_status == 'settlement') {
                $orderPayment->settlement_time = $request->transaction_time;
            }
            $orderPayment->save();

            return response()->json([
                'status' => true,
                'message' => 'Set checkout product success',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
