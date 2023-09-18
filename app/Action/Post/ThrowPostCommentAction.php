<?php

namespace App\Action\Post;

use App\Models\Comment;
use App\Models\Post;
use Carbon\Carbon;

class ThrowPostCommentAction
{
      /**
     * Action handling data.
     */
    public static function throw_post_comment_action($postId)
    {
        $comments = Comment::with(['user:id,first_name,username,foto_profile' , 'sub_comments' => function($subComment) {
            $subComment->with('user:id,first_name,username,foto_profile')->has('user')->select('id' , 'user_id' , 'comment_id' , 'sub_comment' , 'created_at');
        }])
        ->has('user')
        ->where('post_id' , $postId)
        ->select('id' , 'user_id' , 'post_id' , 'comment' , 'created_at')
        ->withCount('sub_comments as total_sub_comments')
        ->latest()
        ->get();
        return $comments;
    }
}