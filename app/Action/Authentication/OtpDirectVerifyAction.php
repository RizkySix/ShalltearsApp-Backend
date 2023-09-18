<?php

namespace App\Action\Authentication;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;


class OtpDirectVerifyAction
{
    /**
     * Action handling direct verify.
     */
    public static function otp_direct_verify_action(string $hash_id , int $otp_code) : bool
    {
        $encodedId = Hashids::decode($hash_id);

        $otpData = DB::table('otp_codes')->where('user_id' , $encodedId)
                                        ->where('otp_code' , $otp_code)
                                        ->where('expired_time' , '>' , Carbon::now());

        $verify = $otpData->first();
        if($verify){
            User::where('id' , $encodedId)->update(['email_verified_at' => Carbon::now()]);
            $otpData->delete();
            return true;
        }

        return false;

    }

}