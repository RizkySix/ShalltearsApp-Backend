<?php

namespace App\Action\Like;

use App\Models\Post;
use App\Trait\NotificationInsertTrait;

class LikeAction
{
    //use NotificationInsertTrait;
      /**
     * Action handling data.
     */
    public static function like_or_dislike_action(Post $post) : bool
    {
        //get is user already like the post?
        $user = auth()->user();
        $isLiked = $user->likes()->where('post_id' , $post->id)->first();

        $currentLike = $post->like;
        if($isLiked){
            $post->update(['like' => $currentLike - 1]);
            $isLiked->delete();
            return false;
        }

        $post->update(['like' => $currentLike + 1]);
        $user->likes()->create(['post_id' => $post->id]);

        //add notification
        NotificationInsertTrait::notification($post , 'menyukai');
       
        return true;
    }
}