<?php

namespace App\Action\User;

use App\Models\Post;
use App\Models\User;

class SpecifiecUserAlbumsAction
{
     /**
     * Action handling data.
     */
    public static function specifiec_user_albums_action(User $user)
    {
        $postsAlbum = Post::with(['album' => function($album) {
            $album->with(['album_photos' => function($albumPhoto) {
                $albumPhoto->orderBy('index' , 'ASC');
            }])
            ->select('id' , 'slug' , 'caption' , 'post_id' , 'created_at');
        }])
        ->select('id', 'uuid' , 'user_id' , 'like' , 'comments' , 'created_at' , 'deleted_at')
        ->where('user_id' , $user->id)
        ->has('album.album_photos')
        ->latest()
        ->get();

        return $postsAlbum;
    }
}