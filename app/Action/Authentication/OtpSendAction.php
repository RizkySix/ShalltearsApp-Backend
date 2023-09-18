<?php

namespace App\Action\Authentication;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OtpSendAction
{
      /**
     * Action handling otp verify.
     */
    public static function send_otp_action(int $user_id , int $otp_code)
    {
        $otpData = DB::table('otp_codes')->where('user_id' , $user_id)
        ->where('otp_code' , $otp_code)
        ->where('expired_time' , '>' , Carbon::now());

        $verify = $otpData->first();
        if($verify){
            User::where('id' , $user_id)->update(['email_verified_at' => Carbon::now()]);
            $otpData->delete();
            return true;
        }

        return false;

    }

}