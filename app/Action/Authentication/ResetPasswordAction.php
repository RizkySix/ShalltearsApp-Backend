<?php

namespace App\Action\Authentication;

use App\Http\Controllers\Authentication\ResetPasswordController;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ResetPasswordAction 
{
    
      /**
     * Action handling reset passwrod.
     */
    public static function reset_password_action(string $email) : bool
    {
        $user = User::select('first_name' , 'id')->where('email' , $email)->first();

        Cache::remember('reset-password-' . $email , now()->addHour(1) , function() {
            return '';
        });

        //reset password
        if($user){
            $newPassword = 'rizky-tampan-' . Str::random(6);
            $token = fake()->uuid();
            $user->update(['reset_password_token' => $token]);
            Cache::put('reset-password-' . $email , $newPassword);
            //payload email
            $data = [
                'newPassword' => $newPassword,
                'firstName' => $user->first_name,
                'reset_password_token' => $token,
                'email' => base64_encode($email)
            ];

           //send email
            Mail::to($email , $user->first_name)->send(new ResetPasswordMail($data));

            return true;

        }
      
        //return false bahwa email tidak valid
        return false;
    }
}