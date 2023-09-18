<?php

namespace App\Http\Controllers\Temporary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteTempAlbumImageController extends Controller
{
    public function __invoke(Request $request)
    {
       $dataImage = DB::table('temp_images')->where('path' , $request->path);
       $isExists = $dataImage->first();
        if($isExists){
            Storage::delete($request->path);
            $dataImage->delete();
        }

        return true;
    }
}
