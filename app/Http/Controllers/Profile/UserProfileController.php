<?php

namespace App\Http\Controllers\Profile;

use App\Action\Profile\UserAvatarUpdateAction;
use App\Action\Profile\UserUpdateProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UserAvatarUpdateRequest;
use App\Http\Requests\Profile\UserProfileRequest;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    /**
     * Handle an incoming profile update request.
     */
    public function update_profile(UserProfileRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();
        $actionResponse = UserUpdateProfileAction::update_profile_action($validatedData);

        if($actionResponse == 1){
            return response()->json([
                'status' => true,
                'message' => 'Success updated',
            ] , 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Failed updated',
            ] , 422);
        }

    }


      /**
     * Handle an incoming profile avatar update request.
     */
    public function avatar_update(UserAvatarUpdateRequest $request) : JsonResponse
    {

        $actionResponse = UserAvatarUpdateAction::avatar_update_action($request);

        if($actionResponse == 1){
            return response()->json([
                'status' => true,
                'message' => 'Success updated avatar',
            ] , 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Failed updated avatar',
            ] , 422);
        }
    }
}
