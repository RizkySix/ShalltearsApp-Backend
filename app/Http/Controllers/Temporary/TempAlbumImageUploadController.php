<?php

namespace App\Http\Controllers\Temporary;

use App\Http\Controllers\Controller;
use App\Http\Requests\Temporary\TempAlbumRequest;
use Illuminate\Support\Facades\DB;

class TempAlbumImageUploadController extends Controller
{
    public function __invoke(TempAlbumRequest $request)
    {
        if($request->file('album_content')){
            $file = $request->file('album_content');
            $path = $file->store('Album/temp');

            DB::table('temp_images')->insert(['path' => $path]);

            //kirim path ke frontend
            return $path;
        }

        return false;
    }
}
