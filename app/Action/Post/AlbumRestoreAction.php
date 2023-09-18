<?php

namespace App\Action\Post;

use App\Models\Post;

class AlbumRestoreAction
{
     /**
     * Action handling data.
     */
    public static function restore_album_action(string $uuid) : bool
    {
       $post = Post::onlyTrashed()->where('uuid' , $uuid)->first();

       if($post){
            $post->restore();

            return true;
       }
        return false;
    }
}