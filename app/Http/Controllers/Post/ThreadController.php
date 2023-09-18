<?php

namespace App\Http\Controllers\Post;

use App\Action\Post\ThreadForceDeleteAction;
use App\Action\Post\ThreadStoreAction;
use App\Action\Post\ThreadUpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\ThreadStoreRequest;
use App\Http\Resources\Post\ThreadResource;
use App\Models\Post;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;

class ThreadController extends Controller
{
    
     /**
     * Handle an incoming thread create request.
     */
    public function store_thread(ThreadStoreRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();

        $actionResponse = ThreadStoreAction::store_thread_action($validatedData);

        if($actionResponse){
            return response()->json([
                'status' => true,
                'message' => 'New thread added',
                'data' => ThreadResource::make($actionResponse)
            ] , 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Something wrong with data',
        ] , 400);
    }


     /**
     * Handle an incoming thread update request.
     */
    public function update_thread(ThreadStoreRequest $request , Thread $thread) : JsonResponse
    {
        $validatedData = $request->validated();

        $actionResponse = ThreadUpdateAction::update_thread_action($validatedData , $thread);

        if($actionResponse !== false){
            return response()->json([
                'status' => true,
                'message' => 'Thread updated',
                'data' => ThreadResource::make($actionResponse)
            ] , 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Cant update to late 1 hours',
        ] , 403);
        
    }


      /**
     * Handle an incoming thread delete request.
     */
    public function force_delete(Post $post) : JsonResponse
    {
        ThreadForceDeleteAction::force_delete_thread_action($post);
        
        return response()->json([
            'status' => true,
            'message' => 'Thread permanently deleted',
        ] , 200);
    }

}
