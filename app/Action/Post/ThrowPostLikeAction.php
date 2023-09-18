<?php

namespace App\Action\Post;

use App\Models\Like;
use App\Models\Post;

class ThrowPostLikeAction
{
      /**
     * Action handling data.
     */
    public static function throw_post_like_action($postId)
    {
        $likes = Like::with(['user:id,first_name,foto_profile,username'])
                    ->has('user')
                    ->where('post_id' , $postId)
                    ->get();
        return $likes;
    }
}