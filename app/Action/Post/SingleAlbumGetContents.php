<?php

namespace App\Action\Post;

use App\Models\Album;

class SingleAlbumGetContents
{
     /**
     * Action handling data.
     */
    public static function single_album_contents_action(Album $album) : object
    {
        return $album->album_photos()->orderBy('index' , 'ASC')->get();

    }
}