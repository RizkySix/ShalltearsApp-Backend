<?php

namespace App\Action\Post;

use App\Models\Post;

class AlbumArchiveAction
{
     /**
     * Action handling data.
     */
    public static function archive_album_action(Post $post) : bool
    {
        $post->delete();
        return true;
    }
}