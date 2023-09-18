<?php

namespace App\Http\Controllers\Comment;

use App\Action\Comment\CommentDeleteAction;
use App\Action\Comment\CommentStoreAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CommentStoreRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
     /**
     * Handle an incoming comment create request.
     */
    public function store_comment(CommentStoreRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();

        $actionResponse = CommentStoreAction::comment_store_action($validatedData);

        if($actionResponse){
            return response()->json([
                'status' => true,
                'message' => 'Comment created',
                'data' => CommentResource::make($actionResponse)
            ] , 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Something wrong with comment data',
        ] , 400);


    }


     /**
     * Handle an incoming comment create request.
     */
    public function delete_comment(Comment $comment) : JsonResponse
    {
        CommentDeleteAction::comment_delete_action($comment);
       
        return response()->json([
            'status' => true,
            'message' => 'Comment permanently deleted',
            'current_comment' => $comment->post->comments
        ] , 200);


    }
}
