<?php

namespace App\Action\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangePasswordAction
{
     /**
     * Action handling CHANGE PASSWORD.
     */
    public static function change_password(array $data) : bool
    {
        //get user
        $user = User::select('password' , 'id')->where('id' , $data['user_id'])->first();

        //cek current password
        if(Hash::check($data['current_password'] , $user->password)){

            //update new password
            $user->update(['password' => Hash::make($data['password'])]);

            return true;
        }

        return false;
    }
}