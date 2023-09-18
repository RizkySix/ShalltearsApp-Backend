<?php

namespace App\Action\Authentication;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LoginAction
{
     /**
     * Action handling login action.
     */
    public static function login_action(array $credentials) : string
    {
      
        //definisikan forever chace
       $cache = Cache::rememberForever('isLoginUser', function () {
            return [] ; 
        });

        //login case email
        if(Auth::attempt(['email' => $credentials['user_mail'] , 'password' => $credentials['password']])){
            $auth = auth()->user();
            $auth->tokens()->delete();
            $token = $auth->createToken('shalltears-app')->plainTextToken;

            //tambah data user
            if(!isset($cache[$auth->id])){
               $cache[$auth->id] = (object)[
                 'first_name' => $auth->first_name,
                 'last_name' => $auth->last_name,
                 'username' => $auth->username,
                 'foto_profile' => $auth->foto_profile,
               ];

               Cache::put('isLoginUser' , $cache);
            }
            return $token;
        }

        //login case username
        if(Auth::attempt(['username' => $credentials['user_mail'] , 'password' => $credentials['password']])){
            $auth = auth()->user();
            $auth->tokens()->delete();
            $token = $auth->createToken('shalltears-app')->plainTextToken;

            //tambah data user
            if(!isset($cache[$auth->id])){
               $cache[$auth->id] = (object)[
                 'first_name' => $auth->first_name,
                 'last_name' => $auth->last_name,
                 'username' => $auth->username,
                 'foto_profile' => $auth->foto_profile,
               ];

               Cache::put('isLoginUser' , $cache);
            }
            return $token;
        }

        return false;
    }
}