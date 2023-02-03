<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ApotekInfo;
use App\Models\ApotekStock;
use App\Models\CartItem;
use App\Models\CustomerAddress;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
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

    public function getMidtransSnapToken(Request $request)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

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
                ],
            ];

            $validator = Validator::make(
                $request->all(),
                [
                    'products' => 'required',
                    'address_id' => 'required',
                    'voucher_id' => 'nullable',
                    'apotek_id' => 'required',
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

            $params['transaction_details']['order_id'] = 'INV_'.Carbon::now()->format('YmdHis').'_'.$user->id;
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
                    'city' => ucwords(strtolower($userAddress->city_name . ', ' . $userAddress->province_name)),
                ],
            ];


            foreach ($request->products as $productReq) {
                $apotekStock = ApotekStock::where('apotek_info_id', $request->apotek_id)
                    ->where('product_id', $productReq['product_id'])
                    ->first();

                $params['transaction_details']['gross_amount'] += (integer) $apotekStock->price;

                array_push($params['item_details'], [
                    'id' => $this->getFirstChar($apotekStock->apotekInfo->name).'-'.$apotekStock->id,
                    'price' => (integer) $apotekStock->price,
                    'quantity' => $productReq['quantity'],
                    'name' => $apotekStock->product->name,
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
