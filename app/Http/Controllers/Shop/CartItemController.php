<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ApotekStock;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CartItemController extends Controller
{
    public function getCartItem()
    {
        try {
            $data = [];
            $cartItems = CartItem::with('product')->where('user_id', Auth('sanctum')->user()->id)->get();

            if (count($cartItems) == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No data found',
                    'data' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            foreach ($cartItems as $cartItem) {
                $apotekStock = ApotekStock::where('product_id', $cartItem->product->id)->get();
                array_push($data, [
                    'id' => $cartItem->id,
                    'quantity' => $cartItem->quantity,
                    'product' => [
                        'id' => $cartItem->product->id,
                        'name' => ucwords($cartItem->product->name),
                        'price' => "Rp. " . number_format($apotekStock->min()->price, 0, null, '.'),
                        'price_int' => (int) $apotekStock->min()->price,
                        'images' => $cartItem->product->images,
                    ],
                    'links' => [
                        'product' => '/shop/product/' . urlencode($cartItem->product->slug),
                        'add_cart' => '/shop/cart/add?product_id=' . $cartItem->product->id,
                        'remove_cart' => '/shop/cart/remove?product_id=' . $cartItem->product->id,
                    ],
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get cart item success',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addCartItem(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'product_id' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $cartItem = CartItem::where('user_id', Auth('sanctum')->user()->id)->where('product_id', $request->product_id)->first();
            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $cartItem->quantity + 1
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'add one cart item success',
                    'data' => $cartItem
                ], Response::HTTP_ALREADY_REPORTED);
            }

            $cartItem = CartItem::create([
                'user_id' => Auth('sanctum')->user()->id,
                'product_id' => $request->product_id,
                'quantity' => 1,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Add cart item success',
                'data' => $cartItem,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateCartItem(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'product_id' => 'required',
                    'quantity' => 'required|integer|min:1',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $cartItem = CartItem::updateOrCreate(
                [
                    'user_id' => Auth('sanctum')->user()->id,
                    'product_id' => $request->product_id,
                ],
                [
                    'quantity' => $request->quantity,
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Update cart item success',
                'data' => $cartItem,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function removeCartItem(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'product_id' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $cartItem = CartItem::where('user_id', Auth('sanctum')->user()->id)->where('product_id', $request->product_id)->first();
            if (!$cartItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Remove cart failed, cart item not found',
                ], Response::HTTP_NOT_FOUND);
            }

            if ($cartItem->quantity > 1) {
                $cartItem->update([
                    'quantity' => $cartItem->quantity - 1,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Remove one cart item success',
                    'data' => $cartItem,
                ], Response::HTTP_OK);
            }

            $cartItem->delete();

            return response()->json([
                'status' => true,
                'message' => 'Delete cart item success',
                'data' => [
                    'id' => $cartItem->id,
                    'user_id' => $cartItem->user_id,
                    'product_id' => $cartItem->product_id,
                    'user_id' => $cartItem->user_id,
                    'quantity' => 0,
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
}
