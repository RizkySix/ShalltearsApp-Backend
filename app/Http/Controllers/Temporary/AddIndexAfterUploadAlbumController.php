<?php

namespace App\Http\Controllers\Temporary;

use App\Http\Controllers\Controller;
use App\Http\Requests\Temporary\AddIndexAlbumRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddIndexAfterUploadAlbumController extends Controller
{
   public function __invoke(AddIndexAlbumRequest $request) : bool
   {
        $validetedData = $request->validated();

        $newOrder = [];

        foreach($validetedData['album_contents'] as $key => $path){
            $newOrder[] = [
                'path' => $path,
                'index' => $key
            ];
        }

        $getTempImg = DB::table('temp_images')->whereIn('path' , $validetedData['album_contents'])->delete();
        DB::table('temp_images')->insert($newOrder);

        return true;
   }
}
