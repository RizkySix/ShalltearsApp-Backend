<?php

namespace App\Http\Controllers\Post;

use App\Action\Post\AlbumPhotoContentDeleteAction;
use App\Http\Controllers\Controller;
use App\Models\AlbumPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlbumPhotoController extends Controller
{
     /**
     * Handle an incoming album photo delete request.
     */
    public function delete_album_photo(AlbumPhoto $content) : JsonResponse
    {
        AlbumPhotoContentDeleteAction::delete_album_content_action($content);

        return response()->json([
            'status' => true,
            'message' => 'Content permanently deleted',
        ] , 200);
    }
}
