<?php

namespace App\Http\Controllers\Post;

use App\Action\Post\ThrowExpandPostAction;
use App\Action\Post\ThrowPostAction;
use App\Action\Post\ThrowPostCommentAction;
use App\Action\Post\ThrowPostLikeAction;
use App\Action\Post\ThrowSingleArchivedPostAction;
use App\Action\Post\ThrowSinglePostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\FetchPostRequest;
use App\Http\Resources\Comment\SpecifiecPostCommentsResource;
use App\Http\Resources\Like\LikeResource;
use App\Http\Resources\Post\PostResource;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
     /**
     * Handle an incoming fetch posts request.
     */
    public function throw_post() : JsonResponse
    {
        $actionResponse = ThrowPostAction::throw_post_action();

        return response()->json([
           'status' => true,
           'message' => 'Post successfully fetched',
           'posts' => PostResource::collection($actionResponse)
        ] ,200);
    }


     /**
     * Handle an incoming fetch posts request.
     */
    public function throw_expand_post(FetchPostRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();
        $actionResponse = ThrowExpandPostAction::throw_expand_post_action($validatedData);

        return response()->json([
           'status' => true,
           'message' => 'Post successfully fetched',
           'posts' => PostResource::collection($actionResponse)
        ] ,200);
    }

    /**
     * Handle an incoming fetch single posts request.
     */
    public function throw_single_post(Post $post) : JsonResponse
    {
        $actionResponse = ThrowSinglePostAction::single_post_action($post);
        $isLikedByUser = Like::where('user_id' , auth()->user()->id)
                            ->where('post_id' , $post->id)
                            ->count();
                            
        return response()->json([
            'status' => true,
            'message' => 'Post successfully fetched',
            'like' => $isLikedByUser ? 'Liked' : 'Unliked',
            'single_post' => PostResource::make($actionResponse)
         ] ,200);

    }


    /**
     * Handle an incoming fetch single archived posts request.
     */
    public function throw_single_archived_post($uuid) : JsonResponse
    {
        $actionResponse = ThrowSingleArchivedPostAction::single_post_action($uuid);
        $isLikedByUser = Like::where('user_id' , auth()->user()->id)
                            ->where('post_id' , $actionResponse->id)
                            ->count();
                            
        return response()->json([
            'status' => true,
            'message' => 'Archived post successfully fetched',
            'like' => $isLikedByUser ? 'Liked' : 'Unliked',
            'single_post' => PostResource::make($actionResponse)
         ] ,200);
    }

      /**
     * Handle an incoming specifiec post comments request.
     */
    public function throw_post_comments($postId) : JsonResponse
    {
        $actionResponse = ThrowPostCommentAction::throw_post_comment_action($postId);

        return response()->json([
            'status' => true,
            'message' => 'Comments successfully fetched',
            'comments_list' => SpecifiecPostCommentsResource::collection($actionResponse)
         ] ,200);
    }

   
       /**
     * Handle an incoming specifiec post like request.
     */
    public function throw_post_likes($postId) : JsonResponse
    {
        $actionResponse = ThrowPostLikeAction::throw_post_like_action($postId);

        return response()->json([
            'status' => true,
            'message' => 'User like successfully fetched',
            'total_like' => $actionResponse->count(),
            'people_likes' => LikeResource::collection($actionResponse)
         ] ,200);

    }

}
