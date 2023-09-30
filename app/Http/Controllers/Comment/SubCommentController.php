<?php

namespace App\Http\Controllers\Comment;

use App\Action\Comment\SubCommentDeleteAction;
use App\Action\Comment\SubCommentStoreAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\SubCommentStoreRequest;
use App\Http\Resources\Comment\SubCommentResource;
use App\Models\SubComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubCommentController extends Controller
{
     /**
     * Handle an incoming sub comment create request.
     */
    public function store_sub_comment(SubCommentStoreRequest $request)
    {
        $validatedData = $request->validated();

        $actionResponse = SubCommentStoreAction::sub_comment_store_action($validatedData);
    
        if($actionResponse){
            return response()->json([
                'status' => true,
                'message' => 'Sub comment created',
                'data' => SubCommentResource::make($actionResponse)
            ] , 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Something wrong with sub comment data',
        ] , 400);
    }


     /**
     * Handle an incoming sub comment delete request.
     */
    public function delete_sub_comment(SubComment $subComment) : JsonResponse
    {
        SubCommentDeleteAction::sub_comment_delete_action($subComment);

        return response()->json([
            'status' => true,
            'message' => 'Sub comment permanently deleted',
            'current_comment' => $subComment->comment->post->comments
        ] , 200);
    }
}
