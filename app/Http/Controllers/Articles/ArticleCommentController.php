<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Models\ArticleComment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ArticleCommentController extends Controller
{
    public function getComments(Request $request, $articlePostId)
    {
        try {
            Carbon::setLocale('id_ID');
            $data = [];
            $nextPageUrl = '';
            
            if(isset($request->parent_id)){
                $comments = ArticleComment::where('article_post_id', $articlePostId)->where('parent_id', $request->parent_id)->latest()->simplePaginate(5);
                $nextPageUrl .= '&parent_id='.$request->parent_id;
            } else {
                $comments = ArticleComment::where('article_post_id', $articlePostId)->whereNull('parent_id')->latest()->simplePaginate(5);
            }

            if(count($comments) === 0){
                return response()->json([
                    'status' => false,
                    'message' => 'Data Not Found',
                    'data' => null,
                ], Response::HTTP_NOT_FOUND);
            }

            foreach ($comments as $comment) {
                $childComment = ArticleComment::where('article_post_id', $articlePostId)->where('parent_id', $comment->id)->get();
                array_push($data, [
                    'user' => [
                        'name' => $comment->user->name,
                        'photo' => $comment->user->photo,
                    ],
                    'body' => $comment->body,
                    'created_at' => $comment->created_at->diffForHumans(),
                    'child_comment_count' => count($childComment),
                    'links' => [
                        'child_comment' => (count($childComment) !== 0) ? '/article/comment/'.$articlePostId.'?parent_id='.$comment->id : null,
                        'reply_comment' => '/article/comment/'.$articlePostId.'/reply?parent_id='.$comment->id,
                    ],
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Get article comments success',
                'data' => [
                    'current' => $comments->currentPage(),
                    'next_page' => (isset($nextPageUrl) && $comments->nextPageUrl()) ? $comments->nextPageUrl().$nextPageUrl : $comments->nextPageUrl(),
                    'comments' => $data,
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

    public function setComment(Request $request, $articlePostId)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'body' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $comment = new ArticleComment([
                'user_id' => Auth::user()->id,
                'article_post_id' => $articlePostId,
                'body' => $request->body,
            ]);
            if(isset($request->parent_id)){
                $comment->parent_id = $request->parent_id;
            }
            $comment->save();

            return response()->json([
                'status' => true,
                'message' => 'Set article comments success',
                'data' => [
                    'name' => $comment->user->name,
                    'photo' => $comment->user->photo,
                    'body' => $comment->body,
                    'created_at' => $comment->created_at->diffForHumans(),
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
}
