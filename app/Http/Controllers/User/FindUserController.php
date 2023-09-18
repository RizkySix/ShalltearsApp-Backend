<?php

namespace App\Http\Controllers\User;

use App\Action\User\FindUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\FindUserRequest;
use App\Http\Resources\User\FindUserResource;
use App\Http\Resources\User\ShallUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FindUserController extends Controller
{
    /**
     * Handle an incoming find user request.
     */
    public function find_user(FindUserRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();
       
        $actionResponse = FindUserAction::find_user_action($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Match users successfully fetched',
            'match_users' => FindUserResource::collection($actionResponse)
         ] ,200);
    }

    /**
     * Handle an incoming find loged in user request.
     */
    public function user_login()
    {
        if(Cache::has('isLoginUser')){
            $data = Cache::get('isLoginUser');
        }else{
            $data = null;
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Login user fetched',
            'login_users' => $data == null ? [] : ShallUserResource::collection($data)
         ] ,200);
    }
}
