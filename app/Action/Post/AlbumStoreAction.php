<?php

namespace App\Action\Post;

use App\Http\Requests\Post\AlbumStoreRequest;
use App\Models\Album;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class AlbumStoreAction
{
     /**
     * Action handling data.
     */
    public static function store_album_action(AlbumStoreRequest $request) : Album
    {
        $validatedData = $request->validated();

        $hashedNum = Hashids::encode(mt_rand(111,999));
        $uuid = Str::random(8) . $hashedNum;

        $post = auth()->user()->posts()->create([
            'uuid' => $uuid
        ]);

         //buat album
         $hashedPostId = Hashids::encode($post->id);
         $slug = Str::random(8) . $hashedPostId;

         $album = $post->album()->create([
            'slug' => $slug,
            'caption' => $validatedData['caption']
         ]);


         //buat albums contents
         $pathAndIndex = [];
         foreach($validatedData['content'] as $key => $path){
            $pathAndIndex[$path] = $key;
         }
        

         $getTempImgs = DB::table('temp_images')->whereIn('path' , array_keys($pathAndIndex));
         $temporaryImg = $getTempImgs->get();
         $insertContents = [];
        
         foreach($temporaryImg as $temp){
         
            $fileName = explode('/' , $temp->path);
            $fileName = end($fileName);
            $newPath = 'Album/' . $request->user()->email . '/' . $fileName;
            $request->reorder ? $index = $pathAndIndex[$temp->path] : $index = $temp->index;
            
            Storage::move($temp->path , $newPath);

            $insertContents[] = [
                'album_id' => $album->id,
                'content' => $newPath,
                'index' => $index
            ];
         }

         $getTempImgs->delete();
         DB::table('album_photos')->insert($insertContents);

         
         return $album;
    }
}