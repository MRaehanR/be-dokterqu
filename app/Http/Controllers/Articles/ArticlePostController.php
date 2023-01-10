<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Models\ArticleLike;
use App\Models\ArticlePost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticlePostController extends Controller
{
    public function getAllArticles(Request $request)
    {
        try {
            $data = [];
            $nextPageUrl = '';

            $articles = ArticlePost::status('published');
            if (isset($request->search)) {
                $articles = $articles->where('title', 'like', "%$request->search%")->orWhere('body', 'like', "%$request->search%");
                $nextPageUrl .= '&search='.urlencode($request->search);
            }
            if (isset($request->category)) {
                $articles = $articles->category($request->category);
                $nextPageUrl .= '&category='.urlencode($request->category);
            }
            $articles = $articles->latest()->simplePaginate(10);

            if (count($articles) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No data found',
                    'data' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            foreach ($articles as $article) {
                array_push($data, [
                    'title' => $article->title,
                    'thumbnail' => $article->thumbnail,
                    'desc' => Str::words(strip_tags($article->body), 10),
                    'category' => ucwords($article->category->name),
                    'slug' => $article->slug,
                    'created_at' => date_format($article->created_at, 'd M Y, H:i'),
                    'links' => [
                        'self' => '/article/post/' . $article->slug,
                        'category' => '/article/post?category='.urlencode($article->category->name),
                    ],
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get all article success',
                'data' => [
                    'current' => $articles->currentPage(),
                    'next_page' => (isset($nextPageUrl) && $articles->nextPageUrl()) ? $articles->nextPageUrl().$nextPageUrl : $articles->nextPageUrl(),
                    'articles' => $data,
                ],
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getArticle($slug)
    {
        try {
            $isLiked = false;
            $article = ArticlePost::where('slug', $slug)->first();

            if (!$article) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Not Found',
                    'data' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            if(Auth('sanctum')->user()){
                $isLiked = ArticleLike::where('article_post_id', $article->id)->where('user_id', Auth('sanctum')->user()->id)->first();
                $isLiked = isset($isLiked);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get article success',
                'data' => [
                    'title' => $article->title,
                    'body' => $article->body,
                    'thumbnail' => $article->thumbnail,
                    'category' => ucwords($article->category->name),
                    'created_at' => date_format($article->created_at, 'd M Y, H:i'),
                    'article_like_count' => $article->like()->count(),
                    'user' => [
                        'isLiked' => $isLiked,
                    ],
                    'links' => [
                        'comment' => '/article/comment/'.$article->id,
                        'like' => '/article/post/'.$article->id.'/like',
                    ]
                ],
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function setArticleLike($articlePostId)
    {
        try {
            $articleLike = ArticleLike::where('user_id', Auth::user()->id)->where('article_post_id', $articlePostId)->first();
            if(!$articleLike){
                $articleLike = ArticleLike::create([
                    'user_id' => Auth::user()->id,
                    'article_post_id' => $articlePostId,
                ]);
    
                return response()->json([
                    'status' => true,
                    'message' => 'Set article like success',
                    'data' => $articleLike,
                ], Response::HTTP_OK);  
            }
            $articleLike->delete();

            return response()->json([
                'status' => true,
                'message' => 'Delete article like success',
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
