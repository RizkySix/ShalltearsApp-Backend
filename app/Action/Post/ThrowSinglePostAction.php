<?php

namespace App\Action\Post;

use App\Models\Post;

class ThrowSinglePostAction
{
      /**
     * Action handling data.
     */
    public static function single_post_action(Post $post) : Post
    {
        $singlePost = $post->load(['album' => function($album) {
            $album->with(['album_photos' => function($albumPhoto) {
                $albumPhoto->orderBy('index' , 'ASC'); // urutkan dari index photo ke 0-4
            }])
            ->select('id' , 'slug' , 'caption' , 'post_id' , 'created_at');
        }]);

        return $singlePost;
    }
  
}