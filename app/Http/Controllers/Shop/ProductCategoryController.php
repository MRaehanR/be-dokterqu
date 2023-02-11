<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ProductCategoryController extends Controller
{
    public function getAllCategory()
    {
        try {
            $data = [];
            $categories = ProductCategory::all();

            foreach ($categories as $category) {
                array_push($data, [
                    'name' => ucwords($category->name),
                    'product_count' => $category->product->count(),
                    'links' => [
                        'products' => '/shop/products?category=' . urlencode($category->name),
                    ]
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get article category success',
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
}
