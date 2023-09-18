<?php


namespace App\Action\Post;

use App\Models\Post;
use App\Models\SubComment;
use Illuminate\Support\Facades\DB;

class ThreadForceDeleteAction
{
      /**
     * Action handling data.
     */
    public static function force_delete_thread_action(Post $post) : bool
    {
       
        DB::transaction(function () use($post) {
            $post->thread()->delete();
            $findComment = $post->comments()->pluck('id');
            SubComment::whereIn('comment_id' , $findComment)->delete();
            $post->comments()->delete();
            $post->likes()->delete();
            $post->forceDelete();
        } , 2);

        return true;

    }
}