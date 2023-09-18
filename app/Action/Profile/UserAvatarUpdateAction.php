<?php

namespace App\Action\Profile;

use App\Http\Requests\Profile\UserAvatarUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class UserAvatarUpdateAction
{
    /**
     * Action handling data.
     */
    public static function avatar_update_action(UserAvatarUpdateRequest $request) : int
    {
        $loginUsers = Cache::get('isLoginUser');
        $auth = $request->user();
        if($request->file('foto_profile')){
            $current_profile = $auth->foto_profile;

            if($current_profile){
                Storage::delete($current_profile);
            }

            $profilePath = $request->file('foto_profile')->store('ProfileImage');
            $user = User::where('id' , $auth->id)->update(['foto_profile' => $profilePath]);
            if($loginUsers){
                $loginUsers[$auth->id] = (object)[
                 'first_name' => $auth->first_name,
                 'last_name' => $auth->last_name,
                 'username' => $auth->username,
                 'foto_profile' => $profilePath,
                ];
                
                Cache::put('isLoginUser' , $loginUsers);
            }
        }

        return $user;
    }
}