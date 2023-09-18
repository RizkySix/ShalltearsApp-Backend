<?php

namespace App\Observers;

use App\Mail\RegisterOtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;

class RegisterObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        DB::table('otp_codes')->where('user_id' , $user->id)->delete();
        $otp_code = mt_rand(111124 , 965149);
        
        //buat data otp code
        DB::table('otp_codes')->insert([
            'user_id' => $user->id,
            'otp_code' => $otp_code,
            'expired_time' => Carbon::now()->addMinutes(120) //expired 2 jam
        ]);

        $hash_id = Hashids::encode($user->id);

        $data = [
            'otp_code' => $otp_code,
            'direct_url' => route('otp.direct.verify' , ['hash_id' => $hash_id , 'otp_code' => $otp_code])
        ];

        //send otp mail
        Mail::to($user->email , $user->first_name)->send(new RegisterOtpMail($data));
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
