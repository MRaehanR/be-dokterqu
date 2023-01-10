<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ArticleCategoryController extends Controller
{
    public function getAllCategory()
    {
        try {
            $data = [];
            $categories = ArticleCategory::all();

            foreach ($categories as $category) {
                array_push($data, [
                    'name' => ucwords($category->name),
                    'article_post_count' => $category->articlePost()->count(),
                    'links' => [
                        'article' => '/article/post?category='.urlencode($category->name),
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
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
