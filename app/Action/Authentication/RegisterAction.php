<?php


namespace App\Action\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class RegisterAction 
{
      /**
     * Action handling registration.
     */
     public static function registration_action(array $data) : string
     {
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $token = $user->createToken('shalltears-app')->plainTextToken;

        $cache = Cache::rememberForever('isLoginUser', function () {
            return [] ; 
         });

         if(!isset($cache[$user->id])){
            $cache[$user->id] = (object)[
              'first_name' => $user->first_name,
              'last_name' => $user->last_name,
              'username' => $user->username,
              'foto_profile' => null,
            ];

            Cache::put('isLoginUser' , $cache);
         }

        return $token;
     }
}