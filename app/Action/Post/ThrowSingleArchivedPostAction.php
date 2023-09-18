<?php

namespace App\Action\Post;

use App\Models\Post;

class ThrowSingleArchivedPostAction
{
      /**
     * Action handling data.
     */
    public static function single_post_action(string $uuid) : Post
    {
        $singlePost = Post::onlyTrashed()->with(['album' => function($album) {
            $album->with(['album_photos' => function($albumPhoto) {
                $albumPhoto->orderBy('index' , 'ASC');
            }])
            ->select('id' , 'slug' , 'caption' , 'post_id' , 'created_at');
        }])
        ->select('id' , 'uuid' , 'deleted_at' , 'like' , 'comments')
        ->where('uuid' , $uuid)
        ->first();

        return $singlePost;
    }
  
}