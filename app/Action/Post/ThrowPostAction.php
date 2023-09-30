<?php

namespace App\Action\Post;

use App\Models\Post;
use Carbon\Carbon;

class ThrowPostAction
{
      /**
     * Action handling data.
     */
    public static function throw_post_action()
    {
       
        $filterDate = Post::whereDate('created_at', '>', now()->subDays(7))->count();
        $day = 7;
    
        //jika tanggal 7 hari terakhir konten nya kurang dari 10 maka set ke 30 hari terakhir
        if($filterDate < 10) {
            $filterDate = Post::whereDate('created_at', '>', now()->subDays(30))->count();
            $day = 30;
        }

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
        ->where(function($query) {
            $query->has('thread')->orHas('album.album_photos');
        })
        ->select('id', 'uuid' , 'user_id' , 'like' , 'comments' , 'created_at' , 'deleted_at')
        ->when($filterDate >= 10 , function ($query) use($day) {
            $query->whereDate('created_at', '>', now()->subDays($day));
        })
        ->latest()
        ->take(10)
        ->get();


        return $posts;
    }
}