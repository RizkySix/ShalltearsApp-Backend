<?php

namespace App\Action\Post;

use App\Models\AlbumPhoto;
use Illuminate\Support\Facades\Storage;

class AlbumPhotoContentDeleteAction
{
     /**
     * Action handling data.
     */
    public static function delete_album_content_action(AlbumPhoto $content) : bool
    {
        Storage::delete($content->content);
        $content->delete();

        return true;
    }
}