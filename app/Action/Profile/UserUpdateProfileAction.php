<?php

namespace App\Action\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserUpdateProfileAction
{
    /**
     * Action handling data.
     */
    public static function update_profile_action(array $data) : int
    {
        $loginUsers = Cache::get('isLoginUser');
        $auth = auth()->user();
        $user = User::where('id' , $auth->id)->update($data);
        
        if($loginUsers && $user == 1){
            $loginUsers[$auth->id] = (object)[
                'first_name' => isset($data['first_name']) ? $data['first_name'] : $auth->first_name,
                'last_name' => isset($data['last_name']) ? $data['last_name'] : $auth->last_name,
                'username' => isset($data['username']) ? $data['username'] : $auth->username,
                'foto_profile' => $loginUsers[$auth->id]->foto_profile ? $loginUsers[$auth->id]->foto_profile : null
              ];

              Cache::put('isLoginUser' , $loginUsers);
        }

        return $user;
    }

}