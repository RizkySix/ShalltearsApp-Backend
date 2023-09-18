<?php

namespace App\Action\User;

use App\Models\User;

class FindUserAction
{
      /**
     * Action handling data.
     */
    public static function find_user_action(array $params)
    {
        $users = User::select('username' , 'first_name' , 'last_name' , 'foto_profile')
        ->when(isset($params['user_keyword']) , function($user) use($params) {
            $user->where('first_name' , 'LIKE' , '%' . $params['user_keyword'] . '%')
            ->orWhere('last_name' , 'LIKE' , '%' . $params['user_keyword'] . '%')
            ->orWhere('username' , 'LIKE' , '%' . $params['user_keyword'] . '%');
         } , function($query) {
                $query->take(6);
         })
            ->when(!isset($params['fetch_all']), function($query) {
                $query->take(6);
            })
            ->get();

        return $users;
    }
}
