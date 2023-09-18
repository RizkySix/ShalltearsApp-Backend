<?php

namespace App\Action\Post;

use App\Models\Album;

class AlbumUpdateAction
{
    /**
     * Action handling data.
     */
    public static function update_album_action(array $data , Album $album) : Album
    {
        $album->update(['caption' => $data['caption']]);

        return $album;

    }
}