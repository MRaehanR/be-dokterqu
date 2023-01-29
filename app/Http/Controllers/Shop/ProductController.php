<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ApotekInfo;
use App\Models\ApotekStock;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

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
                    'image' => $product->image,
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
}
