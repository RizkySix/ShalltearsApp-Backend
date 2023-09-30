<?php

namespace App\Http\Controllers\Like;

use App\Action\Like\LikeAction;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;


class LikeController extends Controller
{
    /**
     * Handle an incoming like request.
     */
    public function like_or_dislike(Post $post) : JsonResponse
    {
        $actionResponse = LikeAction::like_or_dislike_action($post);

        return response()->json([
            'status' => true,
            'message' => 'Liked or Unliked',
            'like' => $actionResponse == true ? 'Liked' : 'Unliked',
            'total_like' => $post->like
        ] , 200);
    }
}
