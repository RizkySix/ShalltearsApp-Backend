<?php

namespace App\Action\Post;

use App\Models\Post;
use Carbon\Carbon;

class ThrowExpandPostAction
{
      /**
     * Action handling data.
     */
    public static function throw_expand_post_action(array $data)
    {
        isset($data['post_filter']) ? $postFilter = $data['post_filter'] : $postFilter = mt_rand(1,999);

        $posts = Post::with(['thread:slug,text,created_at,post_id' , 
        'user:id,first_name,last_name,username,foto_profile' , 
        'album' => function($album) {
            $album->with(['album_photos' => function($albumPhoto) {
                $albumPhoto->orderBy('index' , 'ASC');
            }])
            ->select('id' , 'slug' , 'caption' , 'post_id' , 'created_at');
            }, 'likes' => function($likes) {
            $likes->with(['user:username,id'])->select('id' , 'post_id' , 'user_id');
        }])
        ->has('user')
        ->where(function ($query) {
            $query->has('thread')
                  ->orHas('album.album_photos');
        })
        ->where('id', '<', $postFilter)
        ->latest()
        ->take(10)
        ->get();


        return $posts;
    }
}