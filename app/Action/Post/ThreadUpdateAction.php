<?php


namespace App\Action\Post;

use App\Models\Thread;
use Carbon\Carbon;

class ThreadUpdateAction
{
      /**
     * Action handling data.
     */
    public static function update_thread_action(array $data , Thread $thread) : Thread|bool
    {
        if(now() <= Carbon::parse($thread->created_at)->addHours(1)){
            $thread->update(['text' => $data['text']]);

            return $thread;
        }
        return false;

    }
}