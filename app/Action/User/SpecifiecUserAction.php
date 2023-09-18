<?php

namespace App\Action\User;

use App\Models\Album;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;

class SpecifiecUserAction
{
     /**
     * Action handling data.
     */
    public static function specifiec_user_action(User $user) : array
    {
        //cari total album
        $postsAlbum = Post::has('album.album_photos')->select('id')->where('user_id' , $user->id)->get();
        $albums = Album::whereIn('post_id' , $postsAlbum)->count();

        //cari total thread
        $postsThread = Post::has('thread')->select('id')->where('user_id' , $user->id)->get();
        $threads = Thread::whereIn('post_id' , $postsThread)->count();
        
        $totalAlbumAndThread = [
            'albums' => $albums,
            'threads' => $threads
        ];

        return $totalAlbumAndThread;
    }
}