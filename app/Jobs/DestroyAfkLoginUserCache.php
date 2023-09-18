<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DestroyAfkLoginUserCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //dapatkan data user yang afk selama satu jam
        $getUsers = DB::table('personal_access_tokens')->where('last_used_at' , '<=' , now()->subMinutes(60));

        $afkUsers =  $getUsers->pluck('tokenable_id')->toArray();

        $loginUsers = Cache::get('isLoginUser');

        if($loginUsers){
            $loginUsers = array_diff_key($loginUsers, array_flip($afkUsers)); //menghapus cache untuk afk user
            Cache::put('isLoginUser' , $loginUsers);
        }

        $getUsers->delete();

    }
}
