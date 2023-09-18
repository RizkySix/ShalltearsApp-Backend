<?php

namespace App\Action\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class StoreResetPasswordAction 
{
    
      /**
     * Action handling store reset password.
     */
    public static function store_reset_password_action(string $email) : bool
    { 
       $cache = Cache::get('reset-password-' . $email);
       if($cache){
            User::where('email' , $email)->update(['password' => Hash::make($cache) , 'reset_password_token' => null]);
            Cache::forget('reset-password-' . $email);
            return true;
       }
       
       return false;

    }
}