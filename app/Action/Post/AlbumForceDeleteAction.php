<?php

namespace App\Action\Post;

use App\Models\Post;
use App\Models\SubComment;
use Illuminate\Support\Facades\Storage;

class AlbumForceDeleteAction
{
     /**
     * Action handling data.
     */
    public static function force_delete_album_action(string $uuid) : bool
    {
       $post = Post::withTrashed()->where('uuid' , $uuid)->first();

       if($post){
          $post->load(['album' => function($album) {
               $album->with(['album_photos:id,album_id,content']);
          }]);

            if($post->album){
               foreach($post->album->album_photos as $content){
                    Storage::delete($content->content);
               }
               
               $post->album->album_photos()->delete();
            }
            
            $post->album()->delete();
            
            $findComment = $post->comments()->pluck('id');
            SubComment::whereIn('comment_id' , $findComment)->delete();
            $post->comments()->delete();
            $post->likes()->delete();
            $post->forceDelete();

            return true;
       }
        return false;
    }
}