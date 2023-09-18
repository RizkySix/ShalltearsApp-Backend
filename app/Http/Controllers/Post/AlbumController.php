<?php

namespace App\Http\Controllers\Post;

use App\Action\Post\AlbumArchiveAction;
use App\Action\Post\AlbumForceDeleteAction;
use App\Action\Post\AlbumRestoreAction;
use App\Action\Post\AlbumStoreAction;
use App\Action\Post\AlbumUpdateAction;
use App\Action\Post\SingleAlbumGetContents;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\AlbumStoreRequest;
use App\Http\Requests\Post\UpdateAlbumRequest;
use App\Http\Resources\Post\AlbumPhotosResource;
use App\Http\Resources\Post\AlbumResource;
use App\Models\Album;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class AlbumController extends Controller
{
     /**
     * Handle an incoming album create request.
     */
    public function store_album(AlbumStoreRequest $request) 
    {
          $actionResponse = AlbumStoreAction::store_album_action($request);

          if($actionResponse){
               return response()->json([
                    'status' => true,
                    'message' => 'New Album added',
                    'data' => AlbumResource::make($actionResponse->load(['album_photos']))
                ] , 201);
          }

          return response()->json([
               'status' => false,
               'message' => 'Something wrong with data',
           ] , 400);
    }


      /**
     * Handle an incoming album get contents request.
     */
    public function get_contents_album(Album $album) : JsonResponse
    {
        $actionResponse = SingleAlbumGetContents::single_album_contents_action($album);
        return response()->json([
            'status' => true,
            'message' => 'Contents Fetched',
            'caption' => $album->caption,
            'images' => AlbumPhotosResource::collection($actionResponse)
        ] , 200);
    }


     /**
     * Handle an incoming album update request.
     */
    public function update_album(UpdateAlbumRequest $request , Album $album) : JsonResponse
    {
          $validatedData = $request->validated();

          $actionResponse = AlbumUpdateAction::update_album_action($validatedData  , $album);

          if($actionResponse){
               return response()->json([
                    'status' => true,
                    'message' => 'Album updated',
                    'data' => AlbumResource::make($actionResponse->load(['album_photos']))
                ] , 200);
          }

          return response()->json([
               'status' => false,
               'message' => 'Something wrong with data',
           ] , 400);
    }


     /**
     * Handle an incoming album softdelete request.
     */
    public function archive_album(Post $post) : JsonResponse
    {
         AlbumArchiveAction::archive_album_action($post);

         return response()->json([
          'status' => true,
          'message' => 'Album archived',
      ] , 200);
    }

      /**
     * Handle an incoming album restore request.
     */
    public function restore_album($uuid) : JsonResponse
    {
       $actionResponse = AlbumRestoreAction::restore_album_action($uuid);

       if($actionResponse === true){
          return response()->json([
               'status' => true,
               'message' => 'Album unarchived',
           ] , 200);
       }

       return response()->json([
          'status' => false,
          'message' => 'Failed unarchive',
      ] , 400);
    }



      /**
     * Handle an incoming album restore request.
     */
    public function force_delete_album($uuid) : JsonResponse
    {
          $actionResponse = AlbumForceDeleteAction::force_delete_album_action($uuid);

          if($actionResponse === true){
               return response()->json([
                    'status' => true,
                    'message' => 'Album permanently deleted',
                ] , 200);
            }
     
            return response()->json([
               'status' => false,
               'message' => 'Failed delete album',
           ] , 400);
    }

}
