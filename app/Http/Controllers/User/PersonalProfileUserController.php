<?php

namespace App\Http\Controllers\User;

use App\Action\User\SpecifiecUserAction;
use App\Action\User\SpecifiecUserAlbumsAction;
use App\Action\User\SpecifiecUserArchivedAlbumsAction;
use App\Action\User\SpecifiecUserThreadsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Post\PostResource;
use App\Http\Resources\User\FindUserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalProfileUserController extends Controller
{

    /**
     * Handle an incoming data authenticated user request.
     */
    public function authenticated_user(Request $request) : JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Data of ' . $request->user()->first_name . ' fetched',
            'data' => FindUserResource::make($request->user()),
         ] ,200);
    }

    /**
     * Handle an incoming find specified user request.
     */
    public function specifiec_user(User $user) : JsonResponse
    {
        $actionResponse = SpecifiecUserAction::specifiec_user_action($user);

        return response()->json([
            'status' => true,
            'message' => 'Data of ' . $user->first_name . ' fetched',
            'data' => FindUserResource::make($user),
            'total_albums' => $actionResponse['albums'],
            'total_threads' => $actionResponse['threads'],
         ] ,200);

    }

     /**
     * Handle an incoming find specified user but only for account (created_at and email) request.
     */
    public function specifiec_email_and_created_at() : JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Email and date of joined fetched',
            'data' => [
                'email' => auth()->user()->email,
                'created_at' => Carbon::parse(auth()->user()->created_at)->format('Y M d')
            ]
        ]);
    }

    /**
     * Handle an incoming fetch specified albums of user request.
     */
    public function specifiec_user_albums(User $user) : JsonResponse
    {
        $actionResponse = SpecifiecUserAlbumsAction::specifiec_user_albums_action($user);
     
        return response()->json([
            'status' => true,
            'message' => 'Albums of ' . $user->first_name . ' fetched',
            'posts' => PostResource::collection($actionResponse)
         ] ,200);

    }

      /**
     * Handle an incoming fetch specified threads of user request.
     */
    public function specifiec_user_threads(User $user) : JsonResponse
    {
        $actionResponse = SpecifiecUserThreadsAction::specifiec_user_threads_action($user);

        return response()->json([
            'status' => true,
            'message' => 'Threads of ' . $user->first_name . ' fetched',
            'posts' => PostResource::collection($actionResponse)
         ] ,200);

    }

    
      /**
     * Handle an incoming fetch specified archived albums of user request.
     */
    public function specifiec_user_archived_albums(User $user) : JsonResponse
    {
        $actionResponse = SpecifiecUserArchivedAlbumsAction::specifiec_user_archived_albums_action($user);

        return response()->json([
            'status' => true,
            'message' => 'Archived albums of ' . $user->first_name . ' fetched',
            'posts' => PostResource::collection($actionResponse)
         ] ,200);
    }

}
